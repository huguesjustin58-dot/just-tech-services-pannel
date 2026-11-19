@extends('layouts.app')

@section('title', 'Mon Espace Client — JUST TECH SERVICES')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-[#00E5FF]">Espace Client Isolé</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Bienvenue, {{ $user->name }}</h1>
            <p class="text-sm text-slate-400">Gérez vos abonnements VPN, paiements Mobile Money et configurations Android / iPhone.</p>
        </div>
        <div class="rounded-xl border border-slate-800 bg-[#0F1624] px-4 py-2.5 text-xs text-slate-300">
            Quota Test Gratuit utilisé :
            <strong class="font-mono text-white">{{ $freeTestUsedCount }} / {{ $freeTestMaxPerAccount }}</strong>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-800 bg-[#0F1624] p-6">
                <h2 class="text-lg font-bold text-white">Mes Abonnements &amp; Configurations VPN</h2>
                @if ($licenses->isEmpty())
                    <p class="mt-3 text-sm text-slate-400">Aucun abonnement actif pour le moment. Commandez un forfait ou activez un test gratuit.</p>
                @else
                    <div class="mt-4 space-y-4">
                        @foreach ($licenses as $license)
                            <div class="rounded-xl border border-slate-800 bg-[#090D16] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-white">{{ $license->plan?->name ?? 'Forfait VPN' }}</p>
                                        <p class="font-mono text-xs text-slate-400">
                                            Identifiant SSH : {{ $license->vpn_username }} &bull;
                                            Expire le : {{ $license->expires_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="rounded-md border border-cyan-500/30 bg-cyan-500/10 px-2.5 py-1 font-mono text-xs text-[#00E5FF]">
                                        {{ $license->status->value }}
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <form method="POST" action="{{ route('client.delivery.android', $license) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-[#00E5FF] px-3.5 py-2 text-xs font-bold text-[#090D16] transition hover:bg-cyan-300">
                                            Générer Code Cloud Android
                                        </button>
                                    </form>

                                    <a href="{{ route('client.delivery.iphone', $license) }}" class="rounded-lg border border-slate-700 bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white transition hover:border-[#00E5FF]">
                                        Télécharger Config iPhone (.npvt)
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($cloudCodes->isNotEmpty())
                <div class="rounded-2xl border border-slate-800 bg-[#0F1624] p-6">
                    <h2 class="text-lg font-bold text-white">Mes Codes Cloud Android</h2>
                    <div class="mt-4 space-y-2">
                        @foreach ($cloudCodes as $cloud)
                            <div class="flex items-center justify-between rounded-xl border border-slate-800 bg-[#090D16] px-4 py-3 font-mono text-xs">
                                <span class="font-bold text-[#00E5FF]">{{ $cloud->code }}</span>
                                <span class="text-slate-400">{{ $cloud->status }} &bull; exp. {{ $cloud->expires_at?->format('d/m/Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-800 bg-[#0F1624] p-6">
                <h2 class="text-lg font-bold text-white">Historique de mes Paiements</h2>
                @if ($payments->isEmpty())
                    <p class="mt-3 text-sm text-slate-400">Aucun paiement enregistré sur votre compte.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-800 text-slate-400">
                                <tr>
                                    <th class="pb-2">Facture</th>
                                    <th class="pb-2">Forfait</th>
                                    <th class="pb-2">Montant</th>
                                    <th class="pb-2">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td class="py-2.5 font-mono text-slate-200">{{ $payment->invoice_number }}</td>
                                        <td class="py-2.5 text-slate-300">{{ $payment->plan?->name }}</td>
                                        <td class="py-2.5 font-mono text-white">{{ number_format($payment->amount_fcfa, 0, ',', ' ') }} FCFA</td>
                                        <td class="py-2.5 font-semibold text-[#00E5FF]">{{ $payment->status->label() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-800 bg-[#0F1624] p-6">
                <h2 class="text-lg font-bold text-white">Commander un Forfait</h2>
                <p class="mt-1 text-xs text-slate-400">
                    MTN : {{ $channels['mtn_momo']['number'] }} &bull;
                    Orange : {{ $channels['orange_money']['number'] }} &bull;
                    OM ASSO : {{ $channels['orange_merchant_fallback']['code'] }}
                </p>

                <form method="POST" action="{{ route('client.payments.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="plan_id" class="block text-xs font-semibold text-slate-300">Forfait</label>
                        <select id="plan_id" name="plan_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-[#090D16] px-3 py-2 text-xs text-white">
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} — {{ number_format($plan->price_fcfa, 0, ',', ' ') }} FCFA</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="method" class="block text-xs font-semibold text-slate-300">Mode de paiement</label>
                        <select id="method" name="method" required class="mt-1 w-full rounded-xl border border-slate-700 bg-[#090D16] px-3 py-2 text-xs text-white">
                            <option value="mtn_momo">MTN Mobile Money ({{ $channels['mtn_momo']['number'] }})</option>
                            <option value="orange_money">Orange Money ({{ $channels['orange_money']['number'] }})</option>
                            <option value="orange_om_asso">Paiement Marchand Orange (OM ASSO {{ $channels['orange_merchant_fallback']['code'] }})</option>
                        </select>
                    </div>

                    <div>
                        <label for="external_reference" class="block text-xs font-semibold text-slate-300">ID / Référence transaction</label>
                        <input id="external_reference" name="external_reference" type="text" required placeholder="Ex: MP260503.1234.A98765"
                               class="mt-1 w-full rounded-xl border border-slate-700 bg-[#090D16] px-3 py-2 text-xs text-white focus:border-[#00E5FF] focus:outline-none">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-[#00E5FF] py-2.5 text-xs font-bold text-[#090D16] transition hover:bg-cyan-300">
                        Soumettre le paiement
                    </button>
                </form>
            </div>

            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 p-6">
                <h2 class="text-base font-bold text-white">Offre Test Gratuit (3 Go / 2h)</h2>
                <p class="mt-1 text-xs text-slate-400">1 activation par appareil, maximum 2 par compte client.</p>

                <form method="POST" action="{{ route('client.free-test.claim') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="platform" class="block text-xs font-semibold text-slate-300">Système</label>
                        <select id="platform" name="platform" required class="mt-1 w-full rounded-xl border border-slate-700 bg-[#090D16] px-3 py-2 text-xs text-white">
                            <option value="android">Android (Code Cloud)</option>
                            <option value="iphone">iPhone (.npvt)</option>
                        </select>
                    </div>

                    <div>
                        <label for="device_fingerprint" class="block text-xs font-semibold text-slate-300">Identifiant appareil</label>
                        <input id="device_fingerprint" name="device_fingerprint" type="text" required placeholder="ID unique appareil"
                               class="mt-1 w-full rounded-xl border border-slate-700 bg-[#090D16] px-3 py-2 text-xs text-white focus:border-emerald-400 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full rounded-xl border border-emerald-400/40 bg-emerald-500/20 py-2.5 text-xs font-bold text-emerald-200 transition hover:bg-emerald-500/30">
                        Activer le Test Gratuit
                    </button>
                </form>
            </div>
        </aside>
    </div>
@endsection
