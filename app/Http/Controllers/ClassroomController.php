<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::getActive();
        $yearId = $request->get('academic_year_id', $activeYear?->id);

        $classrooms = Classroom::with(['institution', 'academicYear'])
            ->withCount('studentPlacements')
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($request->get('institution_id'), fn ($q, $v) => $q->where('institution_id', $v))
            ->orderBy('institution_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('classrooms/Index', [
            'classrooms' => $classrooms,
            'institutions' => Institution::all(),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
            'activeYearId' => (int) $yearId,
            'filters' => $request->only(['academic_year_id', 'institution_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'institution_id' => 'required|exists:institutions,id',
            'name' => 'required|string|max:50',
        ]);

        Classroom::create($validated);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $classroom->update($validated);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->studentPlacements()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus kelas yang sudah memiliki siswa.']);
        }

        $classroom->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:classrooms,id',
        ]);

        // Only delete classrooms that have no students
        $deleted = Classroom::whereIn('id', $validated['ids'])
            ->whereDoesntHave('studentPlacements')
            ->delete();

        return back()->with('success', "{$deleted} kelas berhasil dihapus.");
    }
}
