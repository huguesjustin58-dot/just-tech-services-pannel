<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\CloudActivationCode;
use App\Models\License;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Billing\MobileMoneyGatewayService;
use App\Services\Compliance\FreeTestEligibilityService;
use App\Services\Config\AndroidCloudConfigService;
use App\Services\Config\NpvPrototypeCompilerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/**
 * Niveau 2 — Contrôleur Espace Client.
 * Isolation stricte : toutes les requêtes sont filtrées sur $request->user()->id
 * et vérifiées via Policies (LicensePolicy, PaymentPolicy, CloudActivationCodePolicy).
 */
class ClientWorkspaceController extends Controller
{
    public function dashboard(Request $request, MobileMoneyGatewayService $gateway): View
    {
        $user = $request->user();

        $licenses = License::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $payments = Payment::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $cloudCodes = CloudActivationCode::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $plans = Plan::query()
            ->where('is_active', true)
            ->where('price_fcfa', '>', 0)
            ->orderBy('price_fcfa')
            ->get(['id', 'code', 'name', 'price_fcfa', 'duration_days', 'quota_gb', 'max_concurrent_sessions', 'is_vip']);

        $freeTestUsedCount = DB::table('free_test_claims')
            ->where('user_id', $user->id)
            ->count();

        return view('client.dashboard', [
            'user' => $user,
            'licenses' => $licenses,
            'payments' => $payments,
            'cloudCodes' => $cloudCodes,
            'plans' => $plans,
            'channels' => $gateway->getCheckoutChannels(),
            'freeTestUsedCount' => $freeTestUsedCount,
            'freeTestMaxPerAccount' => FreeTestEligibilityService::MAX_PER_ACCOUNT,
        ]);
    }

    public function submitPayment(Request $request): RedirectResponse
    {
        Gate::authorize('create', Payment::class);

        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'method' => ['required', 'in:mtn_momo,orange_money,orange_om_asso'],
            'external_reference' => ['required', 'string', 'min:4', 'max:120'],
        ]);

        $plan = Plan::query()
            ->whereKey($validated['plan_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $user = $request->user();

        Payment::create([
            'invoice_number' => 'JTS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'idempotency_key' => (string) Str::uuid(),
            'user_id' => $user->id,
            'reseller_id' => $user->reseller_id,
            'plan_id' => $plan->id,
            'amount_fcfa' => $plan->price_fcfa,
            'method' => PaymentMethod::from($validated['method']),
            'status' => PaymentStatus::PENDING,
            'external_reference' => trim($validated['external_reference']),
        ]);

        return redirect()
            ->route('client.dashboard')
            ->with('status', 'Paiement soumis pour validation par notre équipe / agent de contrôle.');
    }

    public function claimFreeTest(Request $request, FreeTestEligibilityService $eligibility): RedirectResponse
    {
        $validated = $request->validate([
            'device_fingerprint' => ['required', 'string', 'max:120'],
            'platform' => ['required', 'in:android,iphone'],
        ]);

        $eligibility->assertEligibleAndRecord(
            client: $request->user(),
            deviceFingerprint: $validated['device_fingerprint'],
            platform: $validated['platform'],
        );

        return redirect()
            ->route('client.dashboard')
            ->with('status', 'Offre Test Gratuit (3 Go / 2h) enregistrée pour votre appareil.');
    }

    public function issueAndroidCloud(Request $request, License $license, AndroidCloudConfigService $cloudService): RedirectResponse
    {
        Gate::authorize('view', $license);
        Gate::authorize('create', CloudActivationCode::class);

        try {
            $cloudCode = $cloudService->issueForLicense($license);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('client.dashboard')
                ->withErrors(['delivery' => $exception->getMessage()]);
        }

        return redirect()
            ->route('client.dashboard')
            ->with('status', 'Code Cloud Android généré : '.$cloudCode->code);
    }

    public function downloadIphoneNpv(Request $request, License $license, NpvPrototypeCompilerService $npvCompiler): Response|RedirectResponse
    {
        Gate::authorize('view', $license);

        try {
            $compiled = $npvCompiler->compileForClient($license);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('client.dashboard')
                ->withErrors(['delivery' => $exception->getMessage()]);
        }

        return response($compiled['content'], 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$compiled['filename'].'"',
        ]);
    }
}
