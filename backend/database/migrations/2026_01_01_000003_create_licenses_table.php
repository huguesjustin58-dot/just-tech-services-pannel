<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table): void {
            $table->id();
            $table->string('idempotency_key', 96)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reseller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->foreignId('server_id')->constrained('servers');
            $table->string('vpn_username', 48)->unique();
            $table->text('vpn_password_encrypted');
            $table->string('status', 24)->default('pending')->index();
            $table->bigInteger('bandwidth_quota_bytes');
            $table->bigInteger('bandwidth_used_bytes')->default(0);
            $table->unsignedSmallInteger('max_concurrent_sessions')->default(1);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->index();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['reseller_id', 'status']);
        });

        DB::statement("CREATE INDEX idx_licenses_active_expiry ON licenses (expires_at) WHERE status = 'active'");
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
