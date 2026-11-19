<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Billing\MobileMoneyGatewayService;
use Illuminate\View\View;

class PublicShowcaseController extends Controller
{
    /**
     * Niveau 1 — Vitrine publique accessible à tous sans authentification.
     * Aucune information sensible (serveurs, IP, prototypes bruts) n'est exposée.
     */
    public function index(MobileMoneyGatewayService $gateway): View
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_fcfa')
            ->get([
                'id',
                'code',
                'name',
                'price_fcfa',
                'duration_days',
                'quota_gb',
                'max_concurrent_sessions',
                'is_vip',
            ]);

        return view('welcome', [
            'plans' => $plans,
            'channels' => $gateway->getCheckoutChannels(),
        ]);
    }
}
