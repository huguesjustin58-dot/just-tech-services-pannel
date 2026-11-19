@extends('layouts.app')

@section('title', 'JUST TECH SERVICES TUNNEL — Android & iPhone')

@section('content')
    <section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-br from-[#0F1624] via-[#0B111E] to-[#090D16] p-8 sm:p-12">
        <div class="max-w-3xl">
            <p class="mb-3 font-mono text-xs font-semibold uppercase tracking-widest text-[#00E5FF]">
                ANDROID (CODE CLOUD) &bull; IPHONE (.NPVT TUNNEL)
            </p>
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl">
                JUST TECH SERVICES TUNNEL
            </h1>
            <p class="mt-4 text-base leading-relaxed text-slate-300 sm:text-lg">
                Connectivité VPN haut débit optimisée pour Android et iPhone. Choisissez votre forfait, réglez par MTN Mobile Money ou Orange Money, puis générez instantanément votre configuration depuis votre espace client.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                @guest
                    <a href="{{ route('register') }}" class="rounded-xl bg-[#00E5FF] px-6 py-3 text-sm font-bold text-[#090D16] transition hover:bg-cyan-300">
                        Activer un forfait / Test Gratuit
                    </a>
                    <a href="{{ route('login') }}" class="rounded-xl border border-slate-700 bg-slate-900/60 px-6 py-3 text-sm font-semibold text-white transition hover:border-[#00E5FF]/50">
                        Accéder à mon Espace Client
                    </a>
                @else
                    @if(auth()->user()->isClient())
                        <a href="{{ route('client.dashboard') }}" class="rounded-xl bg-[#00E5FF] px-6 py-3 text-sm font-bold text-[#090D16] transition hover:bg-cyan-300">
                            Ouvrir mon Espace Client
                        </a>
                    @endif
                @endguest
            </div>
        </div>
    </section>

    <section class="mt-12">
        <div class="mb-6 flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
            <div>
                <h2 class="text-2xl font-bold text-white">Nos Forfaits &amp; Tarifs</h2>
                <p class="text-sm text-slate-400">Tarification transparente en FCFA — compatible Android &amp; iPhone.</p>
            </div>
            <span class="font-mono text-xs text-slate-400">Test gratuit limité à 1 appareil / 2 essais max par compte</span>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <article class="flex flex-col justify-between rounded-2xl border {{ $plan->is_vip ? 'border-[#00E5FF]/40 bg-[#0F1624]' : 'border-slate-800 bg-[#0F1624]/80' }} p-6">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-lg font-bold text-white">{{ $plan->name }}</h3>
                            @if ($plan->price_fcfa === 0)
                                <span class="rounded-md bg-emerald-500/15 px-2 py-0.5 font-mono text-xs font-semibold text-emerald-300">
                                    TEST
                                </span>
                            @elseif ($plan->is_vip)
                                <span class="rounded-md bg-[#00E5FF]/15 px-2 py-0.5 font-mono text-xs font-semibold text-[#00E5FF]">
                                    VIP
                                </span>
                            @endif
                        </div>

                        <p class="mt-4 font-mono text-3xl font-extrabold text-white">
                            @if ($plan->price_fcfa === 0)
                                Gratuit
                            @else
                                {{ number_format($plan->price_fcfa, 0, ',', ' ') }} <span class="text-sm font-medium text-slate-400">FCFA</span>
                            @endif
                        </p>

                        <ul class="mt-4 space-y-2 text-sm text-slate-300">
                            <li>Quota : <strong class="text-white">{{ $plan->quota_gb }} Go</strong></li>
                            <li>Durée : <strong class="text-white">{{ $plan->code === 'free_test_3go_2h' ? '2 heures' : $plan->duration_days.' jours' }}</strong></li>
                            <li>Appareils simultanés : <strong class="text-white">{{ $plan->max_concurrent_sessions }}</strong></li>
                            <li>Livraison : <strong class="text-white">Code Cloud Android ou .npvt iPhone</strong></li>
                        </ul>
                    </div>

                    <div class="mt-6">
                        <a href="{{ auth()->check() && auth()->user()->isClient() ? route('client.dashboard') : route('register') }}" class="block w-full rounded-xl border border-slate-700 bg-slate-900/70 py-2.5 text-center text-sm font-semibold text-white transition hover:border-[#00E5FF] hover:text-[#00E5FF]">
                            Choisir ce forfait
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-12 rounded-2xl border border-slate-800 bg-[#0F1624] p-6 sm:p-8">
        <h2 class="text-xl font-bold text-white">Canaux de Paiement Mobile Money</h2>
        <p class="mt-1 text-sm text-slate-400">Effectuez votre dépôt puis soumettez votre référence dans votre Espace Client.</p>

        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-[#FFCC00]/30 bg-[#FFCC00]/5 p-4">
                <p class="font-mono text-xs font-bold uppercase text-[#FFCC00]">{{ $channels['mtn_momo']['label'] }}</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $channels['mtn_momo']['number'] }}</p>
                <p class="text-sm text-slate-300">Titulaire : {{ $channels['mtn_momo']['beneficiary'] }}</p>
            </div>

            <div class="rounded-xl border border-[#FF7900]/30 bg-[#FF7900]/5 p-4">
                <p class="font-mono text-xs font-bold uppercase text-[#FF7900]">{{ $channels['orange_money']['label'] }}</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $channels['orange_money']['number'] }}</p>
                <p class="text-sm text-slate-300">Titulaire : {{ $channels['orange_money']['beneficiary'] }}</p>
            </div>

            <div class="rounded-xl border border-cyan-500/30 bg-cyan-500/5 p-4">
                <p class="font-mono text-xs font-bold uppercase text-[#00E5FF]">{{ $channels['orange_merchant_fallback']['label'] }}</p>
                <p class="mt-2 font-mono text-lg font-bold text-white">Code : {{ $channels['orange_merchant_fallback']['code'] }}</p>
                <p class="text-sm text-slate-300">USSD : {{ $channels['orange_merchant_fallback']['ussd'] }}</p>
            </div>
        </div>
    </section>
@endsection
