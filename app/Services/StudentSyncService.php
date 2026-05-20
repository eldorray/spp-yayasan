<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentPlacement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StudentSyncService
{
    private string $baseUrl;

    /** @var array<string, Classroom> Cache of classrooms by name */
    private array $classroomCache = [];

    public function __construct()
    {
        $this->baseUrl = config('services.sync_api.base_url', 'https://datainduk.ypdhalmadani.sch.id');
    }

    /**
     * Sync students from the external API.
     *
     * @param string $source 'siswa-mi' or 'siswa-smp'
     * @return array{created: int, updated: int, failed: int, errors: array}
     */
    public function sync(string $source): array
    {
        $institution = $this->resolveInstitution($source);

        if (!$institution) {
            return ['created' => 0, 'updated' => 0, 'failed' => 0, 'errors' => ['Instansi tidak ditemukan untuk source: ' . $source]];
        }

        $activeYear = AcademicYear::getActive();

        if (!$activeYear) {
            return ['created' => 0, 'updated' => 0, 'failed' => 0, 'errors' => ['Tidak ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.']];
        }

        $allData = $this->fetchAllPages($source);

        if (isset($allData['error'])) {
            return ['created' => 0, 'updated' => 0, 'failed' => 0, 'errors' => [$allData['error']]];
        }

        // Pre-load existing classrooms for this institution and year
        $this->loadClassroomCache($institution, $activeYear);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($allData as $index => $row) {
            try {
                $mapped = $this->mapFields($row);

                if (empty($mapped['name'])) {
                    $failed++;
                    $errors[] = "Baris #{$index}: nama kosong, dilewati.";
                    continue;
                }

                // Resolve NIS
                $nis = $this->resolveNis($row);

                // Upsert logic: find by NISN first, then by NIS
                $student = null;

                if (!empty($mapped['nisn'])) {
                    $student = Student::where('nisn', $mapped['nisn'])
                        ->where('institution_id', $institution->id)
                        ->first();
                }

                if (!$student && !empty($nis)) {
                    $student = Student::where('nis', $nis)
                        ->where('institution_id', $institution->id)
                        ->first();
                }

                // Auto-detect domicile from alamat
                $domicile = $this->resolveDomicile($mapped['alamat'], $source);

                $data = [
                    'institution_id' => $institution->id,
                    'nis' => $nis,
                    'nisn' => $mapped['nisn'],
                    'name' => $mapped['name'],
                    'jenis_kelamin' => $mapped['jenis_kelamin'],
                    'tempat_lahir' => $mapped['tempat_lahir'],
                    'tanggal_lahir' => $mapped['tanggal_lahir'],
                    'alamat' => $mapped['alamat'],
                    'telpon' => $mapped['telpon'],
                    'nama_ayah' => $mapped['nama_ayah'],
                    'nama_ibu' => $mapped['nama_ibu'],
                    'nama_wali' => $mapped['nama_wali'],
                    'is_active' => $mapped['is_active'],
                    'domicile' => $domicile,
                ];

                if ($student) {
                    $student->update($data);
                    $updated++;
                } else {
                    $student = Student::create($data);
                    $created++;
                }

                // Auto-create classroom and place student
                $classroomName = $this->parseClassroomName($row['tingkat_rombel'] ?? null);
                if ($classroomName && $student) {
                    $classroom = $this->getOrCreateClassroom($classroomName, $institution, $activeYear);
                    $this->placeStudent($student, $classroom, $activeYear);
                }
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "Baris #{$index}: " . $e->getMessage();
            }
        }

        return compact('created', 'updated', 'failed', 'errors');
    }

    /**
     * Fetch all pages from the paginated API.
     */
    private function fetchAllPages(string $source): array
    {
        $allData = [];
        $page = 1;
        $maxPages = 1000;

        do {
            $url = "{$this->baseUrl}/api/{$source}/all";

            $response = Http::timeout(60)->get($url, ['page' => $page]);

            if (!$response->successful()) {
                return ['error' => "API error pada halaman {$page}: HTTP {$response->status()}"];
            }

            $json = $response->json();

            if (!isset($json['data']) || !is_array($json['data'])) {
                return ['error' => "Format response tidak valid pada halaman {$page}."];
            }

            $allData = array_merge($allData, $json['data']);

            $currentPage = $json['current_page'] ?? $page;
            $lastPage = $json['last_page'] ?? $page;
            $nextPageUrl = $json['next_page_url'] ?? null;

            $page++;
        } while ($nextPageUrl !== null && $currentPage < $lastPage && $page <= $maxPages);

        return $allData;
    }

    /**
     * Map API fields to local fields.
     */
    private function mapFields(array $row): array
    {
        return [
            'name' => $this->resolveNama($row),
            'nisn' => $row['nisn'] ?? null,
            'jenis_kelamin' => $this->normalizeGender($row['jenis_kelamin'] ?? null),
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'alamat' => $row['alamat'] ?? null,
            'telpon' => $row['no_telepon'] ?? null,
            'nama_ayah' => $row['nama_ayah_kandung'] ?? null,
            'nama_ibu' => $row['nama_ibu_kandung'] ?? null,
            'nama_wali' => $row['nama_wali'] ?? null,
            'is_active' => $this->resolveStatus($row['status'] ?? null),
        ];
    }

    /**
     * Parse date from various formats.
     */
    private function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        // Handle ISO 8601 format (e.g. "2015-05-04T00:00:00.000000Z")
        if (str_contains($value, 'T')) {
            return substr($value, 0, 10);
        }

        return $value;
    }

    /**
     * Resolve nama: prioritas nama_lengkap, fallback nama.
     */
    private function resolveNama(array $row): ?string
    {
        $nama = $row['nama_lengkap'] ?? $row['nama'] ?? null;

        return $nama ? trim($nama) : null;
    }

    /**
     * Resolve NIS: prioritas NISN, fallback NIK, fallback generate temporary.
     */
    private function resolveNis(array $row): string
    {
        $nisn = $row['nisn'] ?? null;
        $nik = $row['nik'] ?? null;

        // Clean NIK (remove leading apostrophe)
        if ($nik) {
            $nik = ltrim($nik, "'");
        }

        if (!empty($nisn)) {
            return $nisn;
        }

        if (!empty($nik)) {
            return $nik;
        }

        return 'TMP-' . now()->format('YmdHis') . '-' . Str::random(4);
    }

    /**
     * Normalize gender value.
     */
    private function normalizeGender(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = strtolower(trim($value));

        if (in_array($value, ['laki-laki', 'male', 'l'])) {
            return 'L';
        }

        if (in_array($value, ['perempuan', 'female', 'p'])) {
            return 'P';
        }

        return null;
    }

    /**
     * Resolve status to boolean.
     */
    private function resolveStatus(?string $status): bool
    {
        if (!$status) {
            return true;
        }

        return strtolower(trim($status)) === 'aktif';
    }

    /**
     * Resolve domicile from alamat field.
     * For SMP: default is kota_tangerang (gratis), only luar if alamat clearly shows outside.
     * For MI: detect from alamat, default luar_kota_tangerang.
     */
    private function resolveDomicile(?string $alamat, string $source): string
    {
        $isSmp = str_contains($source, 'smp');

        if (!$alamat) {
            return $isSmp ? 'kota_tangerang' : 'luar_kota_tangerang';
        }

        $upperAlamat = strtoupper($alamat);

        if (Str::contains($upperAlamat, 'KOTA TANGERANG')) {
            return 'kota_tangerang';
        }

        // For SMP: default kota_tangerang unless alamat clearly shows another city
        if ($isSmp) {
            return 'kota_tangerang';
        }

        return 'luar_kota_tangerang';
    }

    /**
     * Parse classroom name from tingkat_rombel field.
     * Format: "Kelas 4 - KELAS 4A" → "Kelas 4A"
     */
    private function parseClassroomName(?string $tingkatRombel): ?string
    {
        if (!$tingkatRombel) {
            return null;
        }

        // Format: "Kelas 4 - KELAS 4A" or "Kelas 1 - KELAS 1B"
        if (str_contains($tingkatRombel, ' - ')) {
            $parts = explode(' - ', $tingkatRombel);
            $rombelPart = trim($parts[1] ?? '');

            // "KELAS 4A" → "Kelas 4A"
            if (Str::startsWith(strtoupper($rombelPart), 'KELAS')) {
                $className = trim(Str::after(strtoupper($rombelPart), 'KELAS'));
                return 'Kelas ' . $className;
            }

            return $rombelPart;
        }

        return trim($tingkatRombel);
    }

    /**
     * Load existing classrooms into cache.
     */
    private function loadClassroomCache(Institution $institution, AcademicYear $activeYear): void
    {
        $classrooms = Classroom::where('institution_id', $institution->id)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        foreach ($classrooms as $classroom) {
            $this->classroomCache[$classroom->name] = $classroom;
        }
    }

    /**
     * Get or create a classroom by name.
     */
    private function getOrCreateClassroom(string $name, Institution $institution, AcademicYear $activeYear): Classroom
    {
        if (isset($this->classroomCache[$name])) {
            return $this->classroomCache[$name];
        }

        $classroom = Classroom::firstOrCreate(
            [
                'name' => $name,
                'institution_id' => $institution->id,
                'academic_year_id' => $activeYear->id,
            ],
            ['is_active' => true],
        );

        $this->classroomCache[$name] = $classroom;

        return $classroom;
    }

    /**
     * Place student in classroom for the active year.
     */
    private function placeStudent(Student $student, Classroom $classroom, AcademicYear $activeYear): void
    {
        StudentPlacement::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $activeYear->id,
            ],
            ['classroom_id' => $classroom->id],
        );
    }

    /**
     * Resolve institution from source string.
     */
    private function resolveInstitution(string $source): ?Institution
    {
        if (str_contains($source, 'mi')) {
            return Institution::whereRaw('LOWER(code) = ?', ['mi'])
                ->orWhereRaw('LOWER(name) = ?', ['mi'])
                ->first();
        }

        if (str_contains($source, 'smp')) {
            return Institution::whereRaw('LOWER(code) = ?', ['smp'])
                ->orWhereRaw('LOWER(name) = ?', ['smp'])
                ->first();
        }

        return null;
    }
}
