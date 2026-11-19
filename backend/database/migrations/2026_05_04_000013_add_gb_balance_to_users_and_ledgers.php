<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration additive :
 * - Ajoute le stock de Gigas (gb_balance) aux comptes Revendeurs et Sous-revendeurs.
 * - Ajoute le suivi en Go (gb_delta, balance_after_gb) au grand livre revendeur.
 * - Désactive les anciennes formules illimitées sans supprimer l'historique existant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'gb_balance')) {
                $table->unsignedBigInteger('gb_balance')->default(0)->after('credit_balance_fcfa');
            }
        });

        Schema::table('reseller_credit_ledgers', function (Blueprint $table): void {
            if (! Schema::hasColumn('reseller_credit_ledgers', 'gb_delta')) {
                $table->bigInteger('gb_delta')->default(0)->after('balance_after_fcfa');
            }
            if (! Schema::hasColumn('reseller_credit_ledgers', 'balance_after_gb')) {
                $table->unsignedBigInteger('balance_after_gb')->default(0)->after('gb_delta');
            }
        });

        if (Schema::hasTable('plans')) {
            DB::table('plans')
                ->where('code', 'like', 'unlim_%')
                ->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        Schema::table('reseller_credit_ledgers', function (Blueprint $table): void {
            $cols = array_values(array_filter(
                ['gb_delta', 'balance_after_gb'],
                static fn (string $c): bool => Schema::hasColumn('reseller_credit_ledgers', $c),
            ));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'gb_balance')) {
                $table->dropColumn('gb_balance');
            }
        });
    }
};
