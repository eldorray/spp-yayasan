<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AppSettingController extends Controller
{
    /**
     * Show the application settings form.
     */
    public function edit()
    {
        return Inertia::render('admin/AppSettings', [
            'settings' => [
                'app_name' => Setting::get('app_name', config('app.name')),
                'app_logo_url' => Setting::get('app_logo') ? asset('storage/' . Setting::get('app_logo')) : null,
                'app_favicon_url' => Setting::get('app_favicon') ? asset('storage/' . Setting::get('app_favicon')) : null,
                'app_theme' => Setting::get('app_theme', 'tahoe-slate'),
            ]
        ]);
    }

    /**
     * Update the application settings.
     */
    public function update(Request $request)
    {
        // Check for Reset flag
        if ($request->has('reset') && $request->boolean('reset')) {
            $this->resetSettings();
            return back()->with('success', 'Pengaturan aplikasi berhasil disetel ulang ke default.');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_theme' => 'required|string|in:tahoe-slate,tahoe-blue,emerald-garden,sunset-rose,royal-indigo',
            'app_logo' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,svg',
            'app_favicon' => 'nullable|file|max:1024|mimes:png,ico,svg',
        ]);

        // Save App Name & Theme
        Setting::set('app_name', $validated['app_name']);
        Setting::set('app_theme', $validated['app_theme']);

        // Handle App Logo Upload
        if ($request->hasFile('app_logo')) {
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('app_logo')->store('settings', 'public');
            Setting::set('app_logo', $logoPath);
        }

        // Handle App Favicon Upload
        if ($request->hasFile('app_favicon')) {
            $oldFavicon = Setting::get('app_favicon');
            if ($oldFavicon) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('app_favicon')->store('settings', 'public');
            Setting::set('app_favicon', $faviconPath);
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    /**
     * Reset settings to initial defaults.
     */
    protected function resetSettings()
    {
        // Delete uploaded files
        $logo = Setting::get('app_logo');
        if ($logo) {
            Storage::disk('public')->delete($logo);
        }

        $favicon = Setting::get('app_favicon');
        if ($favicon) {
            Storage::disk('public')->delete($favicon);
        }

        // Remove from DB
        Setting::whereIn('key', ['app_name', 'app_theme', 'app_logo', 'app_favicon'])->delete();
    }
}
