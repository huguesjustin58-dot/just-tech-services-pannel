<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\ResellerCreditLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditLedgerService
{
    public function debitForLicense(
        User $reseller,
        int $amountFcfa,
        string $idempotencyKey,
        string $description,
        ?User $actor = null,
    ): ResellerCreditLedger {
        return DB::transaction(function () use ($reseller, $amountFcfa, $idempotencyKey, $description, $actor): ResellerCreditLedger {
            $existing = ResellerCreditLedger::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $locked = User::withoutGlobalScopes()
                ->whereKey($reseller->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->credit_balance_fcfa < $amountFcfa) {
                throw new RuntimeException("Solde de crédits insuffisant ({$locked->credit_balance_fcfa} FCFA disponibles).");
            }

            $before = $locked->credit_balance_fcfa;
            $after = $before - $amountFcfa;
            $locked->update(['credit_balance_fcfa' => $after]);

            return ResellerCreditLedger::create([
                'idempotency_key' => $idempotencyKey,
                'reseller_id' => $locked->id,
                'actor_id' => $actor?->id ?? $locked->id,
                'entry_type' => 'license_debit',
                'amount_fcfa' => -$amountFcfa,
                'balance_before_fcfa' => $before,
                'balance_after_fcfa' => $after,
                'gb_delta' => 0,
                'balance_after_gb' => (int) $locked->gb_balance,
                'description' => $description,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Attribution directe de Gigas par l'Administrateur à un Revendeur ou Sous-revendeur.
     */
    public function allocateGb(
        User $partner,
        int $gbAmount,
        string $idempotencyKey,
        string $description,
        User $adminActor,
    ): ResellerCreditLedger {
        if ($gbAmount <= 0) {
            throw new RuntimeException('Le volume en Gigas à attribuer doit être positif.');
        }

        return DB::transaction(function () use ($partner, $gbAmount, $idempotencyKey, $description, $adminActor): ResellerCreditLedger {
            $existing = ResellerCreditLedger::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $locked = User::withoutGlobalScopes()
                ->whereKey($partner->id)
                ->lockForUpdate()
                ->firstOrFail();

            $afterGb = ((int) $locked->gb_balance) + $gbAmount;
            $locked->update(['gb_balance' => $afterGb]);

            return ResellerCreditLedger::create([
                'idempotency_key' => $idempotencyKey,
                'reseller_id' => $locked->id,
                'actor_id' => $adminActor->id,
                'entry_type' => 'gb_allocation',
                'amount_fcfa' => 0,
                'balance_before_fcfa' => (int) $locked->credit_balance_fcfa,
                'balance_after_fcfa' => (int) $locked->credit_balance_fcfa,
                'gb_delta' => $gbAmount,
                'balance_after_gb' => $afterGb,
                'description' => $description,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Transfert atomique de Gigas d'un Revendeur parent vers un Sous-revendeur.
     */
    public function transferGbToSubReseller(
        User $parentReseller,
        User $subReseller,
        int $gbAmount,
        string $idempotencyKey,
    ): void {
        if ($gbAmount <= 0) {
            throw new RuntimeException('Le volume de transfert en Gigas doit être supérieur à 0.');
        }

        DB::transaction(function () use ($parentReseller, $subReseller, $gbAmount, $idempotencyKey): void {
            $lockedParent = User::withoutGlobalScopes()->whereKey($parentReseller->id)->lockForUpdate()->firstOrFail();
            $lockedSub = User::withoutGlobalScopes()->whereKey($subReseller->id)->lockForUpdate()->firstOrFail();

            if ((int) $lockedSub->parent_reseller_id !== (int) $lockedParent->id) {
                throw new RuntimeException('Ce sous-revendeur n\'est pas rattaché à ce compte revendeur.');
            }

            if ((int) $lockedParent->gb_balance < $gbAmount) {
                throw new RuntimeException("Stock de Gigas insuffisant ({$lockedParent->gb_balance} Go disponibles).");
            }

            $parentAfter = ((int) $lockedParent->gb_balance) - $gbAmount;
            $subAfter = ((int) $lockedSub->gb_balance) + $gbAmount;

            $lockedParent->update(['gb_balance' => $parentAfter]);
            $lockedSub->update(['gb_balance' => $subAfter]);

            ResellerCreditLedger::create([
                'idempotency_key' => "{$idempotencyKey}-out",
                'reseller_id' => $lockedParent->id,
                'actor_id' => $lockedParent->id,
                'entry_type' => 'gb_transfer_out',
                'amount_fcfa' => 0,
                'balance_before_fcfa' => (int) $lockedParent->credit_balance_fcfa,
                'balance_after_fcfa' => (int) $lockedParent->credit_balance_fcfa,
                'gb_delta' => -$gbAmount,
                'balance_after_gb' => $parentAfter,
                'description' => "Transfert de {$gbAmount} Go vers {$lockedSub->name}",
                'created_at' => now(),
            ]);

            ResellerCreditLedger::create([
                'idempotency_key' => "{$idempotencyKey}-in",
                'reseller_id' => $lockedSub->id,
                'actor_id' => $lockedParent->id,
                'entry_type' => 'gb_transfer_in',
                'amount_fcfa' => 0,
                'balance_before_fcfa' => (int) $lockedSub->credit_balance_fcfa,
                'balance_after_fcfa' => (int) $lockedSub->credit_balance_fcfa,
                'gb_delta' => $gbAmount,
                'balance_after_gb' => $subAfter,
                'description' => "Dotation de {$gbAmount} Go reçue de {$lockedParent->name}",
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Achat d'un forfait client par un Revendeur ou Sous-revendeur en déduisant son stock de Gigas.
     */
    public function debitGbForPlan(
        User $partner,
        Plan $plan,
        string $idempotencyKey,
        string $description,
    ): ResellerCreditLedger {
        $requiredGb = (int) $plan->quota_gb;
        if ($requiredGb <= 0) {
            throw new RuntimeException('Seuls les forfaits avec quota en Gigas peuvent être achetés par un partenaire.');
        }

        return DB::transaction(function () use ($partner, $plan, $requiredGb, $idempotencyKey, $description): ResellerCreditLedger {
            $existing = ResellerCreditLedger::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }

            $locked = User::withoutGlobalScopes()
                ->whereKey($partner->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $locked->gb_balance < $requiredGb) {
                throw new RuntimeException("Stock de Gigas insuffisant ({$locked->gb_balance} Go disponibles pour {$requiredGb} Go requis).");
            }

            $afterGb = ((int) $locked->gb_balance) - $requiredGb;
            $locked->update(['gb_balance' => $afterGb]);

            return ResellerCreditLedger::create([
                'idempotency_key' => $idempotencyKey,
                'reseller_id' => $locked->id,
                'actor_id' => $locked->id,
                'entry_type' => 'plan_gb_purchase',
                'amount_fcfa' => -(int) $plan->price_fcfa,
                'balance_before_fcfa' => (int) $locked->credit_balance_fcfa,
                'balance_after_fcfa' => (int) $locked->credit_balance_fcfa,
                'gb_delta' => -$requiredGb,
                'balance_after_gb' => $afterGb,
                'description' => $description,
                'created_at' => now(),
            ]);
        });
    }
}
