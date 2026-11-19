<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number', 40)->unique();
            $table->string('idempotency_key', 96)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reseller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->bigInteger('amount_fcfa');
            $table->string('method', 32)->index();
            $table->string('status', 24)->default('pending')->index();
            $table->string('external_reference', 96)->nullable()->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('reseller_credit_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->string('idempotency_key', 96)->unique();
            $table->foreignId('reseller_id')->constrained('users');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entry_type', 32)->index();
            $table->bigInteger('amount_fcfa');
            $table->bigInteger('balance_before_fcfa');
            $table->bigInteger('balance_after_fcfa');
            $table->string('description', 255);
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_credit_ledgers');
        Schema::dropIfExists('payments');
    }
};
