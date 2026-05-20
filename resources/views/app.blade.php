@php
    $appSettingTheme = \App\Models\Setting::get('app_theme', 'tahoe-slate');
    $appSettingFavicon = \App\Models\Setting::get('app_favicon');
    $faviconUrl = $appSettingFavicon ? asset('storage/' . $appSettingFavicon) : '/favicon.svg';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        {{-- Dynamic Theme Styling Overrides --}}
        <style>
            @if ($appSettingTheme === 'tahoe-blue')
                :root {
                    --primary: hsl(210, 100%, 40%) !important;
                    --ring: hsl(210, 100%, 40%) !important;
                }
                .dark {
                    --primary: hsl(210, 100%, 60%) !important;
                    --ring: hsl(210, 100%, 60%) !important;
                }
            @elseif ($appSettingTheme === 'emerald-garden')
                :root {
                    --primary: hsl(150, 84%, 30%) !important;
                    --ring: hsl(150, 84%, 30%) !important;
                }
                .dark {
                    --primary: hsl(150, 70%, 50%) !important;
                    --ring: hsl(150, 70%, 50%) !important;
                }
            @elseif ($appSettingTheme === 'sunset-rose')
                :root {
                    --primary: hsl(340, 82%, 52%) !important;
                    --ring: hsl(340, 82%, 52%) !important;
                }
                .dark {
                    --primary: hsl(340, 80%, 65%) !important;
                    --ring: hsl(340, 80%, 65%) !important;
                }
            @elseif ($appSettingTheme === 'royal-indigo')
                :root {
                    --primary: hsl(245, 58%, 51%) !important;
                    --ring: hsl(245, 58%, 51%) !important;
                }
                .dark {
                    --primary: hsl(245, 75%, 70%) !important;
                    --ring: hsl(245, 75%, 70%) !important;
                }
            @endif
        </style>

        <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
        @if(str_ends_with($faviconUrl, '.svg'))
            <link rel="icon" href="{{ $faviconUrl }}" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
