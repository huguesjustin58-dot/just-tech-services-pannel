<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Services\Vpn\VpnTransportResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class ProvisionVpnAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;
    public array $backoff = [10, 30, 90];

    public function __construct(public readonly int $licenseId) {}

    public function handle(VpnTransportResolver $transport): void
    {
        $license = License::withoutGlobalScopes()->with(['server', 'plan'])->findOrFail($this->licenseId);
        if ($license->status === LicenseStatus::REVOKED) {
            return;
        }

        try {
            $plainPassword = Crypt::decryptString($license->vpn_password_encrypted);
            $transport->provisionLicenseAccount($license, $plainPassword);

            $license->update([
                'status' => LicenseStatus::ACTIVE,
                'last_error' => null,
            ]);
        } catch (Throwable $e) {
            $license->update(['last_error' => $e->getMessage()]);
            throw $e;
        }
    }
}
