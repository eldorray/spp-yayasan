<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\Student;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $activeYear = AcademicYear::getActive();

        if (!$activeYear) {
            return Inertia::render('Dashboard', [
                'stats' => null,
                'noActiveYear' => true,
            ]);
        }

        $mi = Institution::where('code', 'mi')->first();
        $smp = Institution::where('code', 'smp')->first();

        // Total income this month
        $totalIncomeThisMonth = Payment::where('academic_year_id', $activeYear->id)
            ->where('status', 'valid')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Income by institution
        $miIncome = Payment::where('academic_year_id', $activeYear->id)
            ->where('status', 'valid')
            ->whereHas('student', fn ($q) => $q->where('institution_id', $mi?->id))
            ->sum('amount');

        $smpIncome = Payment::where('academic_year_id', $activeYear->id)
            ->where('status', 'valid')
            ->whereHas('student', fn ($q) => $q->where('institution_id', $smp?->id))
            ->sum('amount');

        // Unpaid students count
        $unpaidCount = MonthlyBill::where('academic_year_id', $activeYear->id)
            ->where('status', '!=', 'paid')
            ->distinct('student_id')
            ->count('student_id');

        // Today's transactions
        $todayTransactions = Payment::where('academic_year_id', $activeYear->id)
            ->where('status', 'valid')
            ->whereDate('payment_date', today())
            ->count();

        // Total students
        $totalStudents = Student::where('is_active', true)->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalIncomeThisMonth' => $totalIncomeThisMonth,
                'miIncome' => $miIncome,
                'smpIncome' => $smpIncome,
                'unpaidCount' => $unpaidCount,
                'todayTransactions' => $todayTransactions,
                'totalStudents' => $totalStudents,
                'activeYear' => $activeYear->name,
            ],
            'noActiveYear' => false,
        ]);
    }
}
