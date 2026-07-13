<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ActivityBill;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        $payments = Payment::with(['student.institution', 'academicYear', 'creator'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('transaction_number', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"));
                });
            })
            ->when($request->get('payment_method'), fn ($q, $v) => $q->where('payment_method', $v))
            ->when($request->get('date_from'), fn ($q, $v) => $q->where('payment_date', '>=', $v))
            ->when($request->get('date_to'), fn ($q, $v) => $q->where('payment_date', '<=', $v))
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('payments/Index', [
            'payments' => $payments,
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['academic_year_id', 'status', 'search', 'payment_method', 'date_from', 'date_to']),
        ]);
    }

    public function create(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $student = null;
        $monthlyBills = [];
        $activityBills = [];

        if ($request->get('student_id')) {
            $student = Student::with(['institution', 'placements' => fn ($q) => $q->where('academic_year_id', $activeYear?->id)->with('classroom')])
                ->find($request->get('student_id'));

            if ($student && $activeYear) {
                $monthlyBills = MonthlyBill::with('feeRate')
                    ->where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('status', '!=', 'paid')
                    ->orderBy('month')
                    ->get();

                $activityBills = ActivityBill::with('activity')
                    ->where('student_id', $student->id)
                    ->whereHas('activity', fn ($q) => $q->where('academic_year_id', $activeYear->id))
                    ->where('status', '!=', 'paid')
                    ->get();
            }
        }

        // Return JSON for AJAX requests (modal)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'student' => $student,
                'monthlyBills' => $monthlyBills,
                'activityBills' => $activityBills,
                'activeYear' => $activeYear,
            ]);
        }

        return Inertia::render('payments/Create', [
            'student' => $student,
            'monthlyBills' => $monthlyBills,
            'activityBills' => $activityBills,
            'activeYear' => $activeYear,
        ]);
    }

    public function searchStudent(Request $request)
    {
        $search = $request->get('q', '');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $students = Student::with('institution')
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'bill_type' => 'required|in:monthly,activity',
            'bill_id' => 'required|integer',
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,transfer',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Get the bill and its academic year (from the bill itself, so paying a
        // prior-year bill is recorded under the correct year, not the active one).
        if ($validated['bill_type'] === 'monthly') {
            $bill = MonthlyBill::findOrFail($validated['bill_id']);
            $billableType = MonthlyBill::class;
            $academicYearId = $bill->academic_year_id;
        } else {
            $bill = ActivityBill::with('activity')->findOrFail($validated['bill_id']);
            $billableType = ActivityBill::class;
            $academicYearId = $bill->activity->academic_year_id;
        }

        // Validate amount doesn't exceed remaining
        $remaining = $bill->amount - $bill->paid_amount;
        if ($validated['amount'] > $remaining) {
            return back()->withErrors(['amount' => 'Jumlah pembayaran melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').']);
        }

        // Create payment
        $payment = Payment::create([
            'transaction_number' => Payment::generateTransactionNumber(),
            'student_id' => $validated['student_id'],
            'academic_year_id' => $academicYearId,
            'billable_type' => $billableType,
            'billable_id' => $bill->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'valid',
            'created_by' => auth()->id(),
        ]);

        // Update bill
        $bill->paid_amount += $validated['amount'];
        $bill->updateStatus();

        $message = 'Pembayaran berhasil dicatat. No. Transaksi: '.$payment->transaction_number;

        // Stay on the referring page (e.g. student detail) instead of the payments list.
        if ($request->boolean('stay')) {
            return back()->with('success', $message);
        }

        return redirect()->route('payments.index')->with('success', $message);
    }

    public function show(Payment $payment)
    {
        $payment->load(['student.institution', 'academicYear', 'creator', 'billable']);

        return Inertia::render('payments/Show', [
            'payment' => $payment,
        ]);
    }
}
