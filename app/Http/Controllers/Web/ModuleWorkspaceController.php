<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterData\FlightProvider;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\TransportProvider;
use Illuminate\Http\Request;

class ModuleWorkspaceController extends Controller
{
    private const ALLOWED_SECTIONS = ['content', 'structure', 'pricing', 'policies', 'settings'];

    public function accommodation(Request $request, Hotel $hotel, string $section)
    {
        $this->authorizeSection($request, (int) $hotel->company_id, $section, $hotel->owners()->where('users.id', $request->user()->id)->exists());

        return view('pages.module-workspace', [
            'module' => 'Accommodation',
            'section' => $section,
            'entityName' => $hotel->name,
            'entityId' => $hotel->id,
            'sections' => self::ALLOWED_SECTIONS,
            'basePath' => '/accommodations/' . $hotel->id,
            'managePath' => '/accommodations/' . $hotel->id . '/manage',
        ]);
    }

    public function flight(Request $request, FlightProvider $provider, string $section)
    {
        $this->authorizeSection($request, (int) $provider->company_id, $section);

        return view('pages.module-workspace', [
            'module' => 'Flight',
            'section' => $section,
            'entityName' => $provider->name,
            'entityId' => $provider->id,
            'sections' => self::ALLOWED_SECTIONS,
            'basePath' => '/flight-providers/' . $provider->id,
            'managePath' => '/flight-providers/' . $provider->id . '/edit',
        ]);
    }

    public function transport(Request $request, TransportProvider $provider, string $section)
    {
        $this->authorizeSection($request, (int) $provider->company_id, $section);

        return view('pages.module-workspace', [
            'module' => 'Transport',
            'section' => $section,
            'entityName' => $provider->name,
            'entityId' => $provider->id,
            'sections' => self::ALLOWED_SECTIONS,
            'basePath' => '/transport-providers/' . $provider->id,
            'managePath' => '/transport-providers/' . $provider->id . '/edit',
        ]);
    }

    private function authorizeSection(Request $request, int $companyId, string $section, bool $hotelAssigned = false): void
    {
        if (!in_array($section, self::ALLOWED_SECTIONS, true)) {
            abort(404);
        }

        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isHotel()) {
            if (!$hotelAssigned) {
                abort(403, 'Unauthorized section access.');
            }
            return;
        }

        if ((int) $user->company_id !== $companyId) {
            abort(403, 'Unauthorized section access.');
        }
    }
}
