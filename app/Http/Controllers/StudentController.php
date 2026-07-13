<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentPlacement;
use App\Services\StudentSyncService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        $query = Student::with(['institution'])
            ->with(['placements' => fn ($q) => $q->where('academic_year_id', $yearId)->with('classroom')])
            ->whereHas('placements', fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->get('institution_id'), fn ($q, $v) => $q->where('institution_id', $v))
            ->when($request->get('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($request->get('classroom_id'), function ($q, $classroomId) use ($yearId) {
                $q->whereHas('placements', fn ($q) => $q->where('academic_year_id', $yearId)->where('classroom_id', $classroomId));
            })
            ->orderBy('name');

        $perPage = $request->get('per_page', '20');
        $students = $perPage === 'all'
            ? $query->paginate(9999)->withQueryString()
            : $query->paginate((int) $perPage)->withQueryString();

        return Inertia::render('students/Index', [
            'students' => $students,
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'classrooms' => Classroom::with('institution')
                ->where('academic_year_id', $yearId)
                ->orderBy('name')
                ->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['search', 'institution_id', 'academic_year_id', 'classroom_id', 'per_page']),
        ]);
    }

    public function show(Student $student)
    {
        $student->load([
            'institution',
            'placements.classroom',
            'placements.academicYear',
            'monthlyBills.academicYear',
            'monthlyBills.payments' => fn ($q) => $q->where('status', 'valid')->latest('id'),
            'activityBills.activity',
            'activityBills.payments' => fn ($q) => $q->where('status', 'valid')->latest('id'),
        ]);

        return Inertia::render('students/Show', [
            'student' => $student,
            'monthlyBills' => $student->monthlyBills
                ->sortBy([['academic_year_id', 'desc'], ['month', 'asc']])
                ->values(),
            'activityBills' => $student->activityBills
                ->sortByDesc(fn ($b) => $b->activity?->activity_date)
                ->values(),
        ]);
    }

    public function create()
    {
        return Inertia::render('students/Create', [
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'classrooms' => Classroom::with('institution')
                ->where('academic_year_id', AcademicYear::getActive()?->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'nis' => 'required|string|unique:students,nis',
            'nisn' => 'nullable|string|unique:students,nisn',
            'name' => 'required|string|max:255',
            'domicile' => 'required|in:kota_tangerang,luar_kota_tangerang',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $student = Student::create([
            'institution_id' => $validated['institution_id'],
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'name' => $validated['name'],
            'domicile' => $validated['domicile'],
        ]);

        // Place student in classroom if provided
        if (! empty($validated['classroom_id'])) {
            $activeYear = AcademicYear::getActive();
            if ($activeYear) {
                StudentPlacement::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $activeYear->id,
                    'classroom_id' => $validated['classroom_id'],
                ]);
            }
        }

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        $student->load(['institution', 'placements.classroom']);

        return Inertia::render('students/Edit', [
            'student' => $student,
            'institutions' => Institution::all(),
            'classrooms' => Classroom::with('institution')
                ->where('academic_year_id', AcademicYear::getActive()?->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'nis' => 'required|string|unique:students,nis,'.$student->id,
            'nisn' => 'nullable|string|unique:students,nisn,'.$student->id,
            'name' => 'required|string|max:255',
            'domicile' => 'required|in:kota_tangerang,luar_kota_tangerang',
            'is_active' => 'boolean',
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);

        $student->update([
            'institution_id' => $validated['institution_id'],
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'name' => $validated['name'],
            'domicile' => $validated['domicile'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update placement if classroom provided
        if (isset($validated['classroom_id'])) {
            $activeYear = AcademicYear::getActive();
            if ($activeYear) {
                StudentPlacement::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $activeYear->id],
                    ['classroom_id' => $validated['classroom_id']],
                );
            }
        }

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student, Request $request)
    {
        $activeYear = AcademicYear::getActive();

        // Only remove placement for the active year, not the student master data
        if ($activeYear) {
            StudentPlacement::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->delete();
        }

        return back()->with('success', "Siswa {$student->name} dihapus dari tahun ajaran aktif.");
    }

    public function syncFromApi(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|in:siswa-mi,siswa-smp',
        ]);

        $service = new StudentSyncService;
        $result = $service->sync($validated['source']);

        if (! empty($result['errors']) && $result['created'] === 0 && $result['updated'] === 0) {
            return back()->withErrors(['sync' => $result['errors'][0]]);
        }

        $message = "Sinkronisasi selesai. Ditambahkan: {$result['created']}, Diperbarui: {$result['updated']}";
        if ($result['failed'] > 0) {
            $message .= ", Gagal: {$result['failed']}";
        }

        return back()->with('success', $message);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:students,id',
        ]);

        $activeYear = AcademicYear::getActive();

        if ($activeYear) {
            // Only remove placements for the active year, not student master data
            $deleted = StudentPlacement::whereIn('student_id', $validated['ids'])
                ->where('academic_year_id', $activeYear->id)
                ->delete();
        } else {
            $deleted = 0;
        }

        return back()->with('success', "{$deleted} siswa dihapus dari tahun ajaran aktif.");
    }
}
