<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration additive sans perte de données pour l'audit et la traçabilité
 * des connexions dans le cadre de l'accès à 3 niveaux (Visiteur / Client / Admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['last_login_at', 'last_login_ip'],
                static fn (string $col): bool => Schema::hasColumn('users', $col),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
