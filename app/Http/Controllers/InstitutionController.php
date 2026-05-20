<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    public function index()
    {
        $institutions = Institution::withCount('students')->get();

        return Inertia::render('institutions/Index', [
            'institutions' => $institutions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:institutions,code',
        ]);

        Institution::create($validated);

        return back()->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:institutions,code,' . $institution->id,
        ]);

        $institution->update($validated);

        return back()->with('success', 'Instansi berhasil diperbarui.');
    }

    public function destroy(Institution $institution)
    {
        if ($institution->students()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus instansi yang sudah memiliki siswa.']);
        }

        $institution->delete();

        return back()->with('success', 'Instansi berhasil dihapus.');
    }
}
