<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Models\Menu;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'appSettings' => [
                'name' => \App\Models\Setting::get('app_name', config('app.name')),
                'logo' => \App\Models\Setting::get('app_logo') ? asset('storage/' . \App\Models\Setting::get('app_logo')) : null,
                'favicon' => \App\Models\Setting::get('app_favicon') ? asset('storage/' . \App\Models\Setting::get('app_favicon')) : null,
                'theme' => \App\Models\Setting::get('app_theme', 'tahoe-slate'),
            ],
            'auth' => [
                'user' => $user,
                'isAdmin' => fn () => $user?->hasRole('super-admin') ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'navigation' => fn () => $this->getNavigationMenus($user),
            'activeAcademicYear' => fn () => AcademicYear::getActive(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Get the navigation menus visible to the current user.
     *
     * @return array<int, array{title: string, url: string, icon: string}>
     */
    private function getNavigationMenus($user): array
    {
        if (! $user) {
            return [];
        }

        $userRoleNames = $user->getRoleNames();

        return Menu::with('roles')
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->filter(function (Menu $menu) use ($userRoleNames, $user) {
                // If menu has no roles assigned, it's visible to everyone
                if ($menu->roles->isEmpty()) {
                    return true;
                }

                // Super-admin sees everything
                if ($user->hasRole('super-admin')) {
                    return true;
                }

                // Check if user has any of the required roles
                return $menu->roles->pluck('name')->intersect($userRoleNames)->isNotEmpty();
            })
            ->map(fn (Menu $menu) => [
                'title' => $menu->title,
                'url' => $menu->url,
                'icon' => $menu->icon,
            ])
            ->values()
            ->toArray();
    }
}
