@extends('layouts.guest')

@section('title', 'Inscription Client — JUST TECH SERVICES')

@section('content')
    <h1 class="text-2xl font-bold text-white">Créer un compte client</h1>
    <p class="mt-1 text-sm text-slate-400">Inscription réservée aux clients pour commander et gérer vos accès VPN.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-xs text-rose-300">
            <ul class="list-disc space-y-1 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Nom complet</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-1.5 w-full rounded-xl border border-slate-700 bg-[#090D16] px-4 py-2.5 text-sm text-white focus:border-[#00E5FF] focus:outline-none">
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-1.5 w-full rounded-xl border border-slate-700 bg-[#090D16] px-4 py-2.5 text-sm text-white focus:border-[#00E5FF] focus:outline-none">
        </div>

        <div>
            <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Téléphone Mobile Money (optionnel)</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="+237 6..."
                   class="mt-1.5 w-full rounded-xl border border-slate-700 bg-[#090D16] px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-[#00E5FF] focus:outline-none">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Mot de passe (8 caractères min.)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-slate-700 bg-[#090D16] px-4 py-2.5 text-sm text-white focus:border-[#00E5FF] focus:outline-none">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-slate-700 bg-[#090D16] px-4 py-2.5 text-sm text-white focus:border-[#00E5FF] focus:outline-none">
        </div>

        <button type="submit" class="w-full rounded-xl bg-[#00E5FF] py-3 text-sm font-bold text-[#090D16] transition hover:bg-cyan-300">
            Créer mon compte client
        </button>
    </form>

    <p class="mt-6 text-center text-xs text-slate-400">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="font-semibold text-[#00E5FF] hover:underline">Se connecter</a>
    </p>
@endsection
