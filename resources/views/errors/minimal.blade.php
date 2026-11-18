<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="bumblebee">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'Kinerja'))</title>
        <link rel="shortcut icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-400 font-sans antialiased">
        <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5">
                <div class="bg-gradient-to-r from-amber-500 to-rose-500 px-6 py-8 text-center sm:px-10">
                    @yield('icon')
                    <p class="text-6xl font-black tracking-tight text-white sm:text-8xl">@yield('code')</p>
                </div>
                <div class="px-6 py-8 text-center sm:px-10">
                    <div class="text-lg font-semibold uppercase tracking-wide text-slate-800">
                        @yield('message')
                    </div>
                    <div class="mt-6 flex items-center justify-center gap-3">
                        @yield('solusi')
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
