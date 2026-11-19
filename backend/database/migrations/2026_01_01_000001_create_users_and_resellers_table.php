<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable()->index();
            $table->string('password');
            $table->string('role', 24)->default('client')->index();
            $table->foreignId('reseller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_reseller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->bigInteger('credit_balance_fcfa')->default(0);
            $table->unsignedInteger('client_seat_limit')->default(50);
            $table->unsignedSmallInteger('commission_bps')->default(1000);
            $table->boolean('can_create_sub_resellers')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'reseller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
