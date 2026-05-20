<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ReportAiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key', '');
        $this->model = config('services.deepseek.model', 'deepseek-v4-flash');
        $this->baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');
    }

    /**
     * Ask a question about the school finance data.
     */
    public function ask(string $question, array $chatHistory = []): string
    {
        if (empty($this->apiKey)) {
            return 'API key DeepSeek belum dikonfigurasi.';
        }

        $systemPrompt = $this->buildSystemPrompt();

        // Build messages with chat history for context
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Include last 6 messages for context
        $recentHistory = array_slice($chatHistory, -6);
        foreach ($recentHistory as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        // Add current question
        $messages[] = ['role' => 'user', 'content' => $question];

        // Step 1: Ask AI to generate SQL
        $sqlResponse = $this->callApiWithMessages($messages);

        if (!$sqlResponse) {
            return 'Maaf, tidak dapat menghubungi AI. Silakan coba lagi.';
        }

        // Extract SQL from response
        $sql = $this->extractSql($sqlResponse);

        if ($sql) {
            // Auto-fix common MySQL syntax issues
            $sql = $this->fixMysqlSyntax($sql);

            // Step 2: Execute the SQL (read-only)
            $queryResult = $this->executeSafeQuery($sql);

            // If failed, try more aggressive fixes
            if ($queryResult === null) {
                $sql = $this->aggressiveFixSql($sql);
                $queryResult = $this->executeSafeQuery($sql);
            }

            if ($queryResult === null) {
                // Log for debugging
                logger()->warning('AI query still failed after fixes', ['sql' => $sql]);
                return 'Maaf, terjadi kesalahan saat mengambil data. Silakan coba pertanyaan dengan cara lain.';
            }

            if (empty($queryResult)) {
                return "Belum ada data yang sesuai untuk pertanyaan tersebut.";
            }

            // Step 3: Ask AI to format the result as a human-readable answer
            $formattedAnswer = $this->formatResult($question, $sql, $queryResult);

            return $formattedAnswer;
        }

        // No SQL found, return the text response directly
        return $this->cleanResponse($sqlResponse);
    }

    /**
     * Build the system prompt with full database schema context.
     */
    private function buildSystemPrompt(): string
    {
        $summary = $this->getDatabaseSummary();

        $prompt = "Kamu adalah asisten AI untuk aplikasi keuangan sekolah yayasan yang memiliki 2 instansi: MI dan SMP.\n\n";
        $prompt .= "DATABASE SCHEMA (MySQL):\n\n";
        $prompt .= "1. users (id, name, email, email_verified_at, password, current_team_id, two_factor_confirmed_at, created_at, updated_at)\n";
        $prompt .= "   - Pengguna sistem (admin, tata usaha, kepala sekolah)\n\n";
        $prompt .= "2. institutions (id, name, code, created_at, updated_at)\n";
        $prompt .= "   - Instansi: MI (code='mi') dan SMP (code='smp')\n\n";
        $prompt .= "3. academic_years (id, name, is_active, created_at, updated_at)\n";
        $prompt .= "   - Tahun ajaran, contoh: \"2025/2026\", \"2026/2027\". Hanya satu yang is_active=1\n\n";
        $prompt .= "4. classrooms (id, academic_year_id, institution_id, name, is_active, created_at, updated_at)\n";
        $prompt .= "   - Kelas per tahun ajaran dan instansi. Contoh: \"Kelas 1A\", \"Kelas 7B\"\n\n";
        $prompt .= "5. students (id, institution_id, nis, nisn, name, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, telpon, nama_ayah, nama_ibu, nama_wali, domicile, is_active, created_at, updated_at)\n";
        $prompt .= "   - domicile: \"kota_tangerang\" atau \"luar_kota_tangerang\"\n";
        $prompt .= "   - jenis_kelamin: \"L\" atau \"P\"\n\n";
        $prompt .= "6. student_placements (id, student_id, academic_year_id, classroom_id, created_at, updated_at)\n";
        $prompt .= "   - Penempatan siswa ke kelas per tahun ajaran. Satu siswa hanya di satu kelas per tahun ajaran.\n\n";
        $prompt .= "7. fee_rates (id, academic_year_id, institution_id, name, amount, created_at, updated_at)\n";
        $prompt .= "   - Tarif tagihan per tahun ajaran dan instansi. Contoh: \"SPP Bulanan\" Rp 250.000\n\n";
        $prompt .= "8. monthly_bills (id, student_id, academic_year_id, fee_rate_id, month, amount, paid_amount, status, created_at, updated_at)\n";
        $prompt .= "   - Tagihan bulanan per siswa. month: 1-12 (Januari-Desember)\n";
        $prompt .= "   - status: \"unpaid\" (belum bayar), \"partial\" (sebagian), \"paid\" (lunas)\n";
        $prompt .= "   - amount = nominal tagihan, paid_amount = total yang sudah dibayar\n\n";
        $prompt .= "9. payments (id, transaction_number, student_id, academic_year_id, billable_type, billable_id, amount, payment_method, payment_date, notes, status, cancel_reason, created_by, created_at, updated_at)\n";
        $prompt .= "   - Transaksi pembayaran. status: \"valid\" atau \"cancelled\"\n";
        $prompt .= "   - payment_method: \"cash\" atau \"transfer\"\n";
        $prompt .= "   - billable_type: \"App\\Models\\MonthlyBill\" untuk tagihan bulanan, \"App\\Models\\ActivityBill\" untuk kegiatan\n";
        $prompt .= "   - billable_id: ID dari monthly_bills atau activity_bills\n\n";
        $prompt .= "10. activities (id, academic_year_id, institution_id, name, amount, activity_date, description, created_at, updated_at)\n";
        $prompt .= "    - Kegiatan tambahan: study tour, camping, LDK, pesantren kilat, dll\n\n";
        $prompt .= "11. activity_bills (id, activity_id, student_id, amount, paid_amount, status, created_at, updated_at)\n";
        $prompt .= "    - Tagihan kegiatan per siswa. status: \"unpaid\", \"partial\", \"paid\"\n\n";
        $prompt .= "12. menus (id, title, url, icon, order, is_active, created_at, updated_at)\n";
        $prompt .= "    - Menu navigasi sidebar\n\n";
        $prompt .= "13. roles (id, name, guard_name, created_at, updated_at)\n";
        $prompt .= "    - Role pengguna: super-admin, admin, tata-usaha\n\n";
        $prompt .= "14. permissions (id, name, guard_name, created_at, updated_at)\n";
        $prompt .= "    - Permission sistem\n\n";
        $prompt .= "15. model_has_roles (role_id, model_type, model_id)\n";
        $prompt .= "    - Relasi user-role\n\n";
        $prompt .= "16. teams (id, name, slug, is_personal, created_at, updated_at, deleted_at)\n";
        $prompt .= "    - Tim/organisasi\n\n";
        $prompt .= "RELASI PENTING:\n";
        $prompt .= "- students.institution_id → institutions.id\n";
        $prompt .= "- classrooms.institution_id → institutions.id\n";
        $prompt .= "- classrooms.academic_year_id → academic_years.id\n";
        $prompt .= "- student_placements.student_id → students.id\n";
        $prompt .= "- student_placements.academic_year_id → academic_years.id\n";
        $prompt .= "- student_placements.classroom_id → classrooms.id\n";
        $prompt .= "- fee_rates.academic_year_id → academic_years.id\n";
        $prompt .= "- fee_rates.institution_id → institutions.id\n";
        $prompt .= "- monthly_bills.student_id → students.id\n";
        $prompt .= "- monthly_bills.academic_year_id → academic_years.id\n";
        $prompt .= "- monthly_bills.fee_rate_id → fee_rates.id\n";
        $prompt .= "- payments.student_id → students.id\n";
        $prompt .= "- payments.academic_year_id → academic_years.id\n";
        $prompt .= "- payments.created_by → users.id\n";
        $prompt .= "- activities.academic_year_id → academic_years.id\n";
        $prompt .= "- activities.institution_id → institutions.id\n";
        $prompt .= "- activity_bills.activity_id → activities.id\n";
        $prompt .= "- activity_bills.student_id → students.id\n\n";
        $prompt .= "ATURAN BISNIS:\n";
        $prompt .= "- Siswa SMP dengan domicile \"kota_tangerang\" GRATIS biaya bulanan (tidak punya tagihan bulanan)\n";
        $prompt .= "- Siswa SMP dengan domicile \"luar_kota_tangerang\" wajib bayar bulanan\n";
        $prompt .= "- Semua siswa MI wajib bayar bulanan\n";
        $prompt .= "- Pembayaran bisa cicilan (status \"partial\")\n";
        $prompt .= "- Tunggakan = amount - paid_amount pada monthly_bills atau activity_bills\n";
        $prompt .= "- Tahun ajaran aktif adalah yang is_active = 1\n\n";
        $prompt .= "DATA SAAT INI:\n{$summary}\n\n";
        $prompt .= "INSTRUKSI:\n";
        $prompt .= "- SELALU generate SQL query untuk menjawab pertanyaan yang berhubungan dengan data. JANGAN bertanya balik atau minta konfirmasi.\n";
        $prompt .= "- JANGAN memberikan contoh data atau template kosong. Query AKAN dieksekusi otomatis dan hasilnya akan ditampilkan.\n";
        $prompt .= "- Langsung generate SQL yang benar, sistem akan mengeksekusi dan menampilkan hasilnya ke user.\n";
        $prompt .= "- Tulis HANYA satu blok SQL ```sql ... ```. Jangan tulis penjelasan panjang sebelum atau sesudah SQL.\n";
        $prompt .= "- Hanya gunakan SELECT (read-only), JANGAN INSERT/UPDATE/DELETE\n";
        $prompt .= "- Jawab dalam Bahasa Indonesia\n";
        $prompt .= "- Jika pertanyaan tentang tagihan siswa tertentu, gabungkan tagihan bulanan DAN tagihan kegiatan dalam satu query menggunakan UNION ALL\n";
        $prompt .= "- Jika pertanyaan tidak membutuhkan query database, jawab langsung\n";
        $prompt .= "- Format angka uang dengan format Rupiah (Rp)\n";
        $prompt .= "- Untuk query tanggal di MySQL gunakan MONTH(), YEAR(), DATE_FORMAT()\n";
        $prompt .= "- JANGAN gunakan NULLS LAST atau NULLS FIRST (tidak didukung MySQL)\n";
        $prompt .= "- Untuk UNION ALL, pastikan ada spasi/newline sebelum dan sesudahnya\n";
        $prompt .= "- Untuk bulan, ingat: 1=Januari, 2=Februari, 3=Maret, 4=April, 5=Mei, 6=Juni, 7=Juli, 8=Agustus, 9=September, 10=Oktober, 11=November, 12=Desember\n";
        $prompt .= "- Gunakan LIKE '%nama%' untuk pencarian nama siswa agar fleksibel\n";
        $prompt .= "- Saat menampilkan tagihan siswa, tampilkan: jenis tagihan, bulan (jika bulanan), nominal, sudah dibayar, sisa, dan status\n";
        $prompt .= "- Saat ditanya siapa yang sudah/belum bayar, SELALU tampilkan nama siswa, NIS, kelas, dan detail pembayaran\n";
        $prompt .= "- PENTING: Untuk cek siswa sudah bayar atau belum, gunakan tabel monthly_bills (cek status = 'paid' atau paid_amount > 0), BUKAN tabel payments\n";
        $prompt .= "- Tabel payments adalah log transaksi. Tabel monthly_bills.status adalah sumber kebenaran status bayar per bulan\n";
        $prompt .= "- Untuk join kelas siswa, gunakan: students JOIN student_placements ON students.id = student_placements.student_id JOIN classrooms ON student_placements.classroom_id = classrooms.id\n";
        $prompt .= "- Filter tahun ajaran aktif dengan: academic_years.is_active = 1 atau academic_year_id = (SELECT id FROM academic_years WHERE is_active = 1)\n";

        return $prompt;
    }

    /**
     * Get a summary of current database state.
     */
    private function getDatabaseSummary(): string
    {
        $totalStudents = DB::table('students')->where('is_active', true)->count();
        $miStudents = DB::table('students')
            ->join('institutions', 'students.institution_id', '=', 'institutions.id')
            ->where('institutions.code', 'mi')
            ->where('students.is_active', true)
            ->count();
        $smpStudents = DB::table('students')
            ->join('institutions', 'students.institution_id', '=', 'institutions.id')
            ->where('institutions.code', 'smp')
            ->where('students.is_active', true)
            ->count();
        $activeYear = DB::table('academic_years')->where('is_active', true)->first();
        $totalPayments = DB::table('payments')->where('status', 'valid')->count();
        $totalIncome = DB::table('payments')->where('status', 'valid')->sum('amount');
        $totalActivities = DB::table('activities')->count();
        $totalClassrooms = DB::table('classrooms')
            ->where('academic_year_id', $activeYear?->id ?? 0)
            ->count();
        $totalUsers = DB::table('users')->count();

        $yearName = $activeYear?->name ?? 'Tidak ada';

        $lines = [];
        $lines[] = "- Tahun ajaran aktif: {$yearName} (id: " . ($activeYear?->id ?? '?') . ")";
        $lines[] = "- Total siswa aktif: {$totalStudents} (MI: {$miStudents}, SMP: {$smpStudents})";
        $lines[] = "- Total kelas (tahun aktif): {$totalClassrooms}";
        $lines[] = "- Total transaksi (tabel payments) valid: {$totalPayments}";
        $lines[] = "- Total pemasukan (tabel payments): Rp " . number_format($totalIncome, 0, ',', '.');
        $lines[] = "- Total kegiatan: {$totalActivities}";
        $lines[] = "- Total pengguna: {$totalUsers}";

        // Monthly bills summary
        $paidBills = DB::table('monthly_bills')->where('status', 'paid')->count();
        $partialBills = DB::table('monthly_bills')->where('status', 'partial')->count();
        $unpaidBills = DB::table('monthly_bills')->where('status', 'unpaid')->count();
        $lines[] = "- Tagihan bulanan: {$paidBills} lunas, {$partialBills} sebagian, {$unpaidBills} belum bayar";

        return implode("\n", $lines);
    }

    /**
     * Call the DeepSeek API.
     */
    private function callApi(string $systemPrompt, string $userMessage): ?string
    {
        return $this->callApiWithMessages([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ]);
    }

    /**
     * Call the DeepSeek API with full messages array.
     */
    private function callApiWithMessages(array $messages): ?string
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v1/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.1,
                    'max_tokens' => 4000,
                ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json('choices.0.message.content');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Extract SQL query from AI response.
     */
    private function extractSql(string $response): ?string
    {
        // Match ```sql ... ``` blocks (closed)
        if (preg_match('/```sql\s*(.*?)\s*```/s', $response, $matches)) {
            return trim($matches[1]);
        }

        // Match ```sql ... (unclosed — response got cut off)
        if (preg_match('/```sql\s*(SELECT.*|WITH.*)/si', $response, $matches)) {
            return trim($matches[1]);
        }

        // Match ``` ... ``` blocks that look like SQL
        if (preg_match('/```\s*(SELECT.*?)\s*```/si', $response, $matches)) {
            return trim($matches[1]);
        }

        // Match ``` ... (unclosed)
        if (preg_match('/```\s*(SELECT.*|WITH.*)/si', $response, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Execute a read-only SQL query safely.
     */
    private function executeSafeQuery(string $sql): ?array
    {
        // Remove trailing semicolons
        $sql = rtrim(trim($sql), ';');

        // Safety: only allow SELECT or WITH...SELECT statements
        $trimmed = strtoupper(trim($sql));
        if (!str_starts_with($trimmed, 'SELECT') && !str_starts_with($trimmed, 'WITH')) {
            return null;
        }

        // Block dangerous keywords
        $dangerous = ['INSERT ', 'UPDATE ', 'DELETE ', 'DROP ', 'ALTER ', 'CREATE ', 'TRUNCATE ', 'EXEC ', 'EXECUTE ', 'PRAGMA '];
        $upperSql = strtoupper($sql);
        foreach ($dangerous as $keyword) {
            if (str_contains($upperSql, $keyword)) {
                return null;
            }
        }

        try {
            $results = DB::select($sql);

            return array_map(fn ($row) => (array) $row, $results);
        } catch (\Throwable $e) {
            // Log for debugging
            logger()->warning('AI SQL execution failed', ['sql' => $sql, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ask AI to format the query result into a human-readable answer.
     */
    private function formatResult(string $question, string $sql, array $results): string
    {
        $resultJson = json_encode(array_slice($results, 0, 50), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $totalRows = count($results);

        $prompt = "Pertanyaan user: \"{$question}\"\n\n";
        $prompt .= "SQL yang dieksekusi:\n```sql\n{$sql}\n```\n\n";
        $prompt .= "Hasil query ({$totalRows} baris):\n```json\n{$resultJson}\n```\n\n";
        $prompt .= "Berikan jawaban yang ringkas dan mudah dipahami dalam Bahasa Indonesia. Format angka uang dengan Rupiah. Jika hasilnya berupa daftar, tampilkan dalam format yang rapi. Jika hasilnya kosong, sampaikan bahwa tidak ada data yang ditemukan.";

        $response = $this->callApi(
            'Kamu adalah asisten yang memformat hasil query database menjadi jawaban yang mudah dipahami. Jawab dalam Bahasa Indonesia. Format angka uang dengan Rp.',
            $prompt,
        );

        return $response ?? "Ditemukan {$totalRows} hasil, namun gagal memformat jawaban.";
    }

    /**
     * Clean AI response (remove SQL blocks and code for direct text answers).
     */
    private function cleanResponse(string $response): string
    {
        // Remove SQL code blocks (closed and unclosed)
        $cleaned = preg_replace('/```sql\s*.*?```/s', '', $response);
        $cleaned = preg_replace('/```sql\s*.*/s', '', $cleaned ?? $response);
        $cleaned = preg_replace('/```.*?```/s', '', $cleaned ?? $response);
        $cleaned = preg_replace('/```\s*.*/s', '', $cleaned ?? $response);

        $result = trim($cleaned ?? $response);

        // If nothing left after cleaning, return a generic message
        if (empty($result)) {
            return 'Sedang memproses pertanyaan Anda. Silakan coba lagi.';
        }

        return $result;
    }

    /**
     * Get the actual error message from executing a query.
     */
    private function getQueryError(string $sql): string
    {
        $sql = rtrim(trim($sql), ';');

        try {
            DB::select($sql);
            return 'Tidak ada error (query mungkin diblokir oleh safety check)';
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * Fix common MySQL syntax issues from AI-generated SQL.
     */
    private function fixMysqlSyntax(string $sql): string
    {
        // Remove NULLS LAST / NULLS FIRST (PostgreSQL syntax)
        $sql = preg_replace('/\bNULLS\s+(LAST|FIRST)\b/i', '', $sql);

        // Fix missing space before UNION ALL
        $sql = preg_replace('/(\w)(UNION\s+ALL)/i', "$1\n$2", $sql);

        // Fix missing space after closing paren before UNION
        $sql = preg_replace('/\)(UNION)/i', ")\n$1", $sql);

        // Remove trailing unmatched parenthesis
        $open = substr_count($sql, '(');
        $close = substr_count($sql, ')');
        if ($close > $open) {
            $diff = $close - $open;
            for ($i = 0; $i < $diff; $i++) {
                $pos = strrpos($sql, ')');
                if ($pos !== false) {
                    $sql = substr_replace($sql, '', $pos, 1);
                }
            }
        }

        return trim($sql);
    }

    /**
     * More aggressive SQL fixes for retry.
     */
    private function aggressiveFixSql(string $sql): string
    {
        // Remove ORDER BY clause entirely if it causes issues
        $sql = preg_replace('/ORDER\s+BY\s+[^)]*?(;|$)/i', '$1', $sql);

        // Remove LIMIT clause
        $sql = preg_replace('/LIMIT\s+\d+/i', '', $sql);

        // Ensure proper UNION ALL spacing
        $sql = preg_replace('/\)\s*UNION\s+ALL\s*SELECT/i', ")\nUNION ALL\nSELECT", $sql);

        return trim(rtrim($sql, ';'));
    }
}
