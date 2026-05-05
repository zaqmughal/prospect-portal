<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-gray-50 text-gray-900 antialiased">
        <!-- Skip to main content for accessibility -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary-600 text-white px-4 py-2 rounded-md z-50">
            Skip to main content
        </a>

        <div class="min-h-screen bg-gray-50">
            <livewire:layout.navigation />

            @if (config('app.beta_banner_enabled'))
                <div class="border-b border-amber-200 bg-amber-50">
                    <div class="mx-auto flex max-w-7xl items-start gap-3 px-4 py-3 text-sm text-amber-900 sm:px-6 lg:px-8" role="status" aria-live="polite">
                        <span class="inline-flex rounded-full bg-amber-200 px-2 py-0.5 text-xs font-semibold tracking-wide text-amber-900">
                            Beta
                        </span>
                        <p>
                            You are using an early version of {{ config('app.name') }}. Features and data may change while we continue improving the platform.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main id="main-content">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
