<?php

namespace App\Http\Controllers\Pricing;

use App\Http\Controllers\Controller;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\TransportProvider;
use App\Services\Pricing\Policies\PricingPolicyAdapterRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingPolicyController extends Controller
{
    public function __construct(
        private readonly PricingPolicyAdapterRegistry $registry
    ) {
    }

    public function show(Request $request, string $module, int $providerId): JsonResponse
    {
        $this->authorizeProvider($request, $module, $providerId);

        return response()->json([
            'data' => $this->registry->resolve($module, $providerId),
        ]);
    }

    private function authorizeProvider(Request $request, string $module, int $providerId): void
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return;
        }

        $companyId = match ($module) {
            'accommodation' => Hotel::query()->whereKey($providerId)->value('company_id'),
            'flight' => FlightProvider::query()->whereKey($providerId)->value('company_id'),
            'transport' => TransportProvider::query()->whereKey($providerId)->value('company_id'),
            default => null,
        };

        if (!$companyId || (int) $companyId !== (int) $user->company_id) {
            abort(403, 'Unauthorized provider access.');
        }
    }
}
