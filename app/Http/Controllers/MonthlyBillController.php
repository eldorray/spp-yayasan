<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\FeeRate;
use App\Models\Institution;
use App\Models\MonthlyBill;
use App\Models\Student;
use App\Models\StudentPlacement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonthlyBillController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        $query = MonthlyBill::with([
            'student.institution',
            'student.placements' => fn ($q) => $q->where('academic_year_id', $yearId)->with('classroom'),
            'feeRate',
        ])
            ->where('academic_year_id', $yearId)
            ->when($request->get('institution_id'), function ($q, $institutionId) {
                $q->whereHas('student', fn ($q) => $q->where('institution_id', $institutionId));
            })
            ->when($request->get('month'), fn ($q, $month) => $q->where('month', $month))
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->get('classroom_id'), function ($q, $classroomId) use ($yearId) {
                $q->whereHas('student.placements', fn ($q) => $q->where('academic_year_id', $yearId)->where('classroom_id', $classroomId));
            })
            ->when($request->get('search'), function ($q, $search) {
                $q->whereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
            })
            ->orderBy('month')
            ->orderBy('student_id');

        $perPage = $request->get('per_page', '25');
        $bills = $perPage === 'all'
            ? $query->paginate(9999)->withQueryString()
            : $query->paginate((int) $perPage)->withQueryString();

        // Summary stats
        $summaryQuery = MonthlyBill::where('academic_year_id', $yearId);
        $totalBilled = (clone $summaryQuery)->sum('amount');
        $totalPaid = (clone $summaryQuery)->sum('paid_amount');
        $unpaidCount = (clone $summaryQuery)->where('status', 'unpaid')->count();
        $partialCount = (clone $summaryQuery)->where('status', 'partial')->count();
        $paidCount = (clone $summaryQuery)->where('status', 'paid')->count();

        return Inertia::render('monthly-bills/Index', [
            'bills' => $bills,
            'summary' => [
                'totalBilled' => $totalBilled,
                'totalPaid' => $totalPaid,
                'totalUnpaid' => $totalBilled - $totalPaid,
                'unpaidCount' => $unpaidCount,
                'partialCount' => $partialCount,
                'paidCount' => $paidCount,
            ],
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'classrooms' => Classroom::where('academic_year_id', $yearId)->orderBy('name')->get(),
            'feeRates' => FeeRate::where('academic_year_id', $yearId)->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['academic_year_id', 'institution_id', 'month', 'status', 'classroom_id', 'search', 'per_page']),
        ]);
    }

    /**
     * Generate monthly bills for students.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'fee_rate_id' => 'required|exists:fee_rates,id',
            'months' => 'required|array|min:1',
            'months.*' => 'integer|between:1,12',
            'institution_id' => 'required|exists:institutions,id',
            'target' => 'required|in:all,classroom',
            'classroom_id' => 'required_if:target,classroom|nullable|exists:classrooms,id',
        ]);

        $feeRate = FeeRate::findOrFail($validated['fee_rate_id']);
        $activeYear = AcademicYear::getActive();

        if (!$activeYear) {
            return back()->withErrors(['error' => 'Tidak ada tahun ajaran aktif.']);
        }

        // Get eligible students based on institution and target
        $studentQuery = StudentPlacement::where('academic_year_id', $activeYear->id)
            ->whereHas('classroom', fn ($q) => $q->where('institution_id', $validated['institution_id']));

        if ($validated['target'] === 'classroom' && $validated['classroom_id']) {
            $studentQuery->where('classroom_id', $validated['classroom_id']);
        }

        $studentIds = $studentQuery->pluck('student_id');

        // For SMP: exclude students with domicile kota_tangerang
        $institution = Institution::find($validated['institution_id']);
        $isSmp = $institution && strtolower($institution->code) === 'smp';
        if ($isSmp) {
            $studentIds = Student::whereIn('id', $studentIds)
                ->where('domicile', '!=', 'kota_tangerang')
                ->pluck('id');
        }

        // Only active students
        $studentIds = Student::whereIn('id', $studentIds)
            ->where('is_active', true)
            ->pluck('id');

        $created = 0;
        $skipped = 0;

        foreach ($studentIds as $studentId) {
            foreach ($validated['months'] as $month) {
                $exists = MonthlyBill::where('student_id', $studentId)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('fee_rate_id', $feeRate->id)
                    ->where('month', $month)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                MonthlyBill::create([
                    'student_id' => $studentId,
                    'academic_year_id' => $activeYear->id,
                    'fee_rate_id' => $feeRate->id,
                    'month' => $month,
                    'amount' => $feeRate->amount,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                ]);

                $created++;
            }
        }

        $message = "Tagihan berhasil di-generate. Dibuat: {$created}";
        if ($skipped > 0) {
            $message .= ", Dilewati (sudah ada): {$skipped}";
        }

        return back()->with('success', $message);
    }

    /**
     * Store a new fee rate.
     */
    public function storeFeeRate(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'institution_id' => 'required|exists:institutions,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        // Check for duplicate
        $exists = FeeRate::where('academic_year_id', $validated['academic_year_id'])
            ->where('institution_id', $validated['institution_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Tarif dengan nama yang sama sudah ada untuk instansi dan tahun ajaran ini.']);
        }

        FeeRate::create($validated);

        return back()->with('success', 'Tarif tagihan berhasil ditambahkan.');
    }

    /**
     * Update a fee rate.
     */
    public function updateFeeRate(Request $request, FeeRate $feeRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
        ]);

        $feeRate->update($validated);

        return back()->with('success', 'Tarif tagihan berhasil diperbarui.');
    }

    /**
     * Delete a fee rate.
     */
    public function destroyFeeRate(FeeRate $feeRate)
    {
        if ($feeRate->monthlyBills()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus tarif yang sudah memiliki tagihan.']);
        }

        $feeRate->delete();

        return back()->with('success', 'Tarif tagihan berhasil dihapus.');
    }

    /**
     * Bulk delete monthly bills.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:monthly_bills,id',
        ]);

        // Only delete unpaid bills
        $deleted = MonthlyBill::whereIn('id', $validated['ids'])
            ->where('status', 'unpaid')
            ->delete();

        return back()->with('success', "{$deleted} tagihan berhasil dihapus.");
    }
}
