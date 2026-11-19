@extends('layouts.guest')

@section('title', 'Connexion — JUST TECH SERVICES')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#00E5FF]/40 bg-[#00E5FF]/10 font-mono text-sm font-bold text-[#00E5FF]">
            JTS
        </span>
        <div>
            <h1 class="text-xl font-bold text-white">Connexion à votre espace</h1>
            <p class="text-xs text-slate-400">Authentification sécurisée — redirection automatique selon votre profil.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-300">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                Adresse e-mail
            </label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   placeholder="nom@exemple.com"
                   class="mt-1.5 w-full rounded-xl border {{ $errors->has('email') ? 'border-rose-500/60' : 'border-slate-700' }} bg-[#090D16] px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-[#00E5FF] focus:outline-none">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                Mot de passe
            </label>
            <div class="relative mt-1.5">
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl border {{ $errors->has('password') ? 'border-rose-500/60' : 'border-slate-700' }} bg-[#090D16] px-4 py-2.5 pr-16 text-sm text-white placeholder-slate-500 focus:border-[#00E5FF] focus:outline-none">
                <button type="button"
                        onclick="const p=document.getElementById('password'); const s=p.type==='password'; p.type=s?'text':'password'; this.textContent=s?'Masquer':'Voir';"
                        class="absolute inset-y-0 right-2 my-1 rounded-lg px-2 text-[11px] font-medium text-slate-400 hover:text-[#00E5FF]">
                    Voir
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 text-xs text-slate-300">
            <input type="checkbox" name="remember" class="rounded border-slate-700 bg-[#090D16] text-[#00E5FF]">
            Maintenir la session active
        </label>

        <button type="submit" class="w-full rounded-xl bg-[#00E5FF] py-3 text-sm font-bold text-[#090D16] transition hover:bg-cyan-300">
            Se connecter
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-400">
        Pas encore client ?
        <a href="{{ route('register') }}" class="font-semibold text-[#00E5FF] hover:underline">Créer un compte client</a>
    </p>
@endsection
