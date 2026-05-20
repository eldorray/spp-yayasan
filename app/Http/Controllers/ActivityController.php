<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Activity;
use App\Models\ActivityBill;
use App\Models\Classroom;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentPlacement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        $activities = Activity::with(['institution', 'academicYear'])
            ->withCount('bills')
            ->withSum('bills', 'amount')
            ->withSum('bills', 'paid_amount')
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->get('institution_id'), fn ($q, $v) => $q->where('institution_id', $v))
            ->orderByDesc('activity_date')
            ->get();

        return Inertia::render('activities/Index', [
            'activities' => $activities,
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'classrooms' => Classroom::where('academic_year_id', $yearId)->orderBy('name')->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['academic_year_id', 'institution_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'institution_id' => 'required|exists:institutions,id',
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'activity_date' => 'nullable|date',
            'description' => 'nullable|string',
            'target' => 'required|in:all,classroom',
            'classroom_ids' => 'required_if:target,classroom|array',
        ]);

        $activity = Activity::create([
            'academic_year_id' => $validated['academic_year_id'],
            'institution_id' => $validated['institution_id'],
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'activity_date' => $validated['activity_date'],
            'description' => $validated['description'],
        ]);

        // Create bills based on target
        $studentIds = collect();
        if ($validated['target'] === 'all') {
            $studentIds = StudentPlacement::where('academic_year_id', $validated['academic_year_id'])
                ->whereHas('classroom', fn ($q) => $q->where('institution_id', $validated['institution_id']))
                ->pluck('student_id');
        } elseif ($validated['target'] === 'classroom' && !empty($validated['classroom_ids'])) {
            $studentIds = StudentPlacement::where('academic_year_id', $validated['academic_year_id'])
                ->whereIn('classroom_id', $validated['classroom_ids'])
                ->pluck('student_id');
        }

        foreach ($studentIds as $studentId) {
            ActivityBill::create([
                'activity_id' => $activity->id,
                'student_id' => $studentId,
                'amount' => $activity->amount,
            ]);
        }

        $count = $studentIds->count();

        return back()->with('success', "Kegiatan berhasil ditambahkan. Tagihan dibuat untuk {$count} siswa.");
    }

    public function show(Activity $activity)
    {
        $activity->load(['institution', 'academicYear']);
        $bills = ActivityBill::with([
            'student.institution',
            'student.placements' => fn ($q) => $q->where('academic_year_id', $activity->academic_year_id)->with('classroom'),
        ])
            ->where('activity_id', $activity->id)
            ->orderBy('student_id')
            ->get();

        return Inertia::render('activities/Show', [
            'activity' => $activity,
            'bills' => $bills,
        ]);
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:0',
            'activity_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $activity->update($validated);

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }

    /**
     * Bulk delete activity bills (only unpaid).
     */
    public function bulkDestroyBills(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:activity_bills,id',
        ]);

        $deleted = ActivityBill::whereIn('id', $validated['ids'])
            ->where('status', 'unpaid')
            ->delete();

        return back()->with('success', "{$deleted} tagihan kegiatan berhasil dihapus.");
    }
}
