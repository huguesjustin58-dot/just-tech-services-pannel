<?php

declare(strict_types=1);

namespace App\Services\Vpn;

use App\Models\Server;
use RuntimeException;

class ServerLoadBalancer
{
    public function selectOptimalServer(?string $preferredRegion = null): Server
    {
        $query = Server::query()
            ->where('is_active', true)
            ->whereColumn('active_connections', '<', 'max_connections');

        if ($preferredRegion !== null) {
            $regional = (clone $query)->where('region_code', $preferredRegion)->get();
            if ($regional->isNotEmpty()) {
                return $regional->sortBy(fn (Server $s): float => $s->loadScore())->first();
            }
        }

        $server = $query->get()->sortBy(fn (Server $s): float => $s->loadScore())->first();
        if (! $server) {
            throw new RuntimeException('Aucun serveur VPN disponible avec capacité libre.');
        }

        return $server;
    }
}
