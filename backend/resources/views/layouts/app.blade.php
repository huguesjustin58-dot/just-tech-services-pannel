<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JUST TECH SERVICES PANEL')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        jts: {
                            bg: '#090D16',
                            surface: '#0F1624',
                            border: '#1E293B',
                            cyan: '#00E5FF',
                            orange: '#FF7900',
                            mtn: '#FFCC00',
                        },
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-[#090D16] text-slate-100 antialiased selection:bg-[#00E5FF]/20 selection:text-[#00E5FF]">
    @include('layouts.navigation')

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} JUST TECH SERVICES TUNNEL &mdash; Infrastructure VPN Android &amp; iPhone.
    </footer>
</body>
</html>
