<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Services\ReportAiService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        // Summary stats
        $totalBilled = MonthlyBill::where('academic_year_id', $yearId)->sum('amount');
        $totalPaid = MonthlyBill::where('academic_year_id', $yearId)->sum('paid_amount');
        $totalUnpaid = $totalBilled - $totalPaid;

        $paidCount = MonthlyBill::where('academic_year_id', $yearId)->where('status', 'paid')->count();
        $partialCount = MonthlyBill::where('academic_year_id', $yearId)->where('status', 'partial')->count();
        $unpaidCount = MonthlyBill::where('academic_year_id', $yearId)->where('status', 'unpaid')->count();

        // Monthly income breakdown
        $monthlyIncome = Payment::where('academic_year_id', $yearId)
            ->where('status', 'valid')
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return Inertia::render('reports/Index', [
            'summary' => [
                'totalBilled' => $totalBilled,
                'totalPaid' => $totalPaid,
                'totalUnpaid' => $totalUnpaid,
                'paidCount' => $paidCount,
                'partialCount' => $partialCount,
                'unpaidCount' => $unpaidCount,
            ],
            'monthlyIncome' => $monthlyIncome,
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'classrooms' => Classroom::where('academic_year_id', $yearId)->orderBy('name')->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['academic_year_id']),
        ]);
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'history' => 'nullable|array',
            'history.*.role' => 'required|string|in:user,assistant',
            'history.*.content' => 'required|string',
            'provider' => 'nullable|string|in:deepseek,gemini',
            'model' => 'nullable|string|max:50',
        ]);

        $provider = $validated['provider'] ?? 'deepseek';
        $model = $validated['model'] ?? null;
        $service = new ReportAiService($provider, $model);
        $answer = $service->ask($validated['question'], $validated['history'] ?? []);

        return response()->json(['answer' => $answer]);
    }
}
