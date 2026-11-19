<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion — JUST TECH SERVICES')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-[#090D16] px-4 py-10 text-slate-100 antialiased">
    <a href="{{ route('home') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold tracking-wide text-[#00E5FF] hover:text-cyan-300">
        <span>&larr; Retour à la vitrine JUST TECH SERVICES</span>
    </a>

    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-[#0F1624] p-8 shadow-2xl shadow-cyan-950/20">
        @yield('content')
    </div>
</body>
</html>
