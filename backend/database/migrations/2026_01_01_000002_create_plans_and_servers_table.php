<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 80);
            $table->bigInteger('price_fcfa');
            $table->bigInteger('reseller_cost_fcfa');
            $table->unsignedInteger('duration_days');
            $table->unsignedInteger('quota_gb');
            $table->unsignedSmallInteger('max_concurrent_sessions')->default(1);
            $table->jsonb('allowed_protocols');
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('servers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('region_code', 16)->default('EU-FR');
            $table->string('public_ip', 45)->unique();
            $table->string('hostname', 120)->nullable();
            $table->unsignedSmallInteger('ssh_port')->default(22);
            $table->unsignedSmallInteger('wss_port')->default(443);
            $table->unsignedSmallInteger('slowdns_port')->default(53);
            $table->string('slowdns_ns_domain', 120)->nullable();
            $table->text('slowdns_public_key')->nullable();
            $table->string('agent_endpoint', 180)->default('/run/jts-agent/agent.sock');
            $table->unsignedInteger('max_connections')->default(500);
            $table->unsignedInteger('active_connections')->default(0);
            $table->decimal('cpu_usage_percent', 5, 2)->default(0);
            $table->decimal('ram_usage_percent', 5, 2)->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
        Schema::dropIfExists('plans');
    }
};
