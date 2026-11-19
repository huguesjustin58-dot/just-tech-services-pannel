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
        Schema::create('vpn_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('protocol', 24)->index();
            $table->string('target_app', 32)->default('npv_tunnel');
            $table->string('visibility', 16)->default('public')->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('download_token', 64)->unique();
            $table->jsonb('template_payload');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_vpn_configs_payload_gin ON vpn_configs USING GIN (template_payload)');

        Schema::create('dns_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('server_id')->constrained('servers')->cascadeOnDelete();
            $table->string('record_type', 8);
            $table->string('fqdn', 160);
            $table->string('content', 180);
            $table->string('cloudflare_record_id', 64)->nullable();
            $table->boolean('proxied')->default(false);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('auditable_type', 120)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->jsonb('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('dns_records');
        Schema::dropIfExists('vpn_configs');
    }
};
