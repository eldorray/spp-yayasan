<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('is_active')
            ->orderByDesc('name')
            ->get();

        return Inertia::render('academic-years/Index', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:20|unique:academic_years,name',
        ]);

        AcademicYear::create($validated);

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:20|unique:academic_years,name,' . $academicYear->id,
        ]);

        $academicYear->update($validated);

        return back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activate(AcademicYear $academicYear)
    {
        // Deactivate all
        AcademicYear::where('is_active', true)->update(['is_active' => false]);

        // Activate selected
        $academicYear->update(['is_active' => true]);

        return back()->with('success', 'Tahun ajaran ' . $academicYear->name . ' berhasil diaktifkan.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        if ($academicYear->is_active) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif.']);
        }

        $academicYear->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
