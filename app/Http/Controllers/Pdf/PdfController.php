<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Itinerary\Itinerary;
use App\Models\Itinerary\ItineraryItem;
use App\Models\ItineraryTemplate;
use App\Models\MasterData\DestinationFee;
use App\Models\MasterData\HotelRate;
use App\Services\CostSheetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function __construct(
        private CostSheetService $costSheetService,
    ) {}

    /**
     * Download a full itinerary PDF (client-facing, day-by-day).
     */
    public function itinerary(Request $request, Itinerary $itinerary)
    {
        $this->authorizeAccess($itinerary);

        $data = $this->buildPdfData($itinerary);

        $pdf = Pdf::loadView('pdf.itinerary', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Itinerary-' . str_replace(' ', '_', $itinerary->client_name) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download a quotation PDF (client-facing pricing).
     */
    public function quotation(Request $request, Itinerary $itinerary)
    {
        $this->authorizeAccess($itinerary);

        $data = $this->buildPdfData($itinerary);

        $pdf = Pdf::loadView('pdf.quotation', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Quotation-QT-' . str_pad($itinerary->id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Download a cost sheet PDF (internal, confidential).
     */
    public function costSheet(Request $request, Itinerary $itinerary)
    {
        $this->authorizeAccess($itinerary);

        $data = $this->buildPdfData($itinerary);

        $pdf = Pdf::loadView('pdf.cost-sheet', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'CostSheet-CS-' . str_pad($itinerary->id, 4, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build all the data needed by PDF templates.
     */
    private function buildPdfData(Itinerary $itinerary): array
    {
        $itinerary->load('days.items');

        $user = auth()->user();
        $companyId = $user->role === 'super_admin'
            ? $itinerary->company_id
            : $user->company_id;

        $company = Company::findOrFail($companyId);

        $template = ItineraryTemplate::where('company_id', $companyId)
            ->where('is_default', true)
            ->first();

        // Fallback to a blank template if none exists
        if (!$template) {
            $template = new ItineraryTemplate([
                'name' => 'Default',
                'primary_color' => '#4f46e5',
                'font' => 'Helvetica',
                'layout_type' => 'classic',
            ]);
        }

        $costSheet = $this->costSheetService->generate($itinerary);
        $previewDays = $itinerary->days->map(function ($day) {
            return [
                'day_number' => $day->day_number,
                'date' => $day->date,
                'items' => $day->items->map(function ($item) {
                    return [
                        'type' => $item->type,
                        'type_label' => $this->itemTypeLabel($item->type),
                        'label' => $this->itemLabel($item),
                        'quantity' => (int) $item->quantity,
                        'image_path' => $this->itemImagePath($item),
                    ];
                })->values(),
            ];
        })->values();

        $quotationRows = $itinerary->days->flatMap(function ($day) use ($itinerary) {
            return $day->items->map(function ($item) use ($day, $itinerary) {
                $cost = (float) $item->cost;
                $markupPct = (float) ($itinerary->markup_percentage ?? 0);
                $selling = $markupPct > 0 ? round($cost * (1 + $markupPct / 100), 2) : $cost;

                return [
                    'day_number' => $day->day_number,
                    'type_label' => $this->itemTypeLabel($item->type),
                    'label' => $this->itemLabel($item),
                    'image_path' => $this->itemImagePath($item),
                    'amount' => $selling,
                ];
            });
        })->values();

        return [
            'itinerary' => $itinerary,
            'company' => $company,
            'template' => $template,
            'costSheet' => $costSheet,
            'previewDays' => $previewDays,
            'quotationRows' => $quotationRows,
        ];
    }

    private function itemTypeLabel(string $type): string
    {
        return match ($type) {
            'hotel' => 'Hotel',
            'transport' => 'Transport',
            'park_fee' => 'Park',
            'flight' => 'Flight',
            'activity' => 'Activity',
            'extra' => 'Extra',
            default => ucfirst($type),
        };
    }

    private function itemLabel(ItineraryItem $item): string
    {
        $ref = $item->reference();

        return match ($item->type) {
            'hotel' => $ref ? ($ref->hotel?->name . ' - ' . $ref->roomType?->type . ', ' . $ref->mealPlan?->name) : 'Accommodation',
            'transport' => $ref ? ($ref->name . ' (' . $ref->capacity . ' pax)') : 'Transport',
            'park_fee' => $ref ? ($ref->destination?->name . ' - ' . $ref->fee_type) : 'Park Fee',
            'flight' => $ref ? ($ref->name . ' - ' . $ref->origin . ' to ' . $ref->destination) : 'Flight',
            'activity' => $ref?->name ?? 'Activity',
            'extra' => $ref?->name ?? 'Extra',
            default => ucfirst($item->type),
        };
    }

    private function itemImagePath(ItineraryItem $item): ?string
    {
        if ($item->type === 'hotel') {
            $rate = HotelRate::with('hotel.accommodationMedia')->find($item->reference_id);
            if (!$rate || !$rate->hotel) {
                return null;
            }

            $cover = $rate->hotel->accommodationMedia->firstWhere('is_cover', true)
                ?? $rate->hotel->accommodationMedia->first();

            return $cover?->file_path;
        }

        if ($item->type === 'park_fee') {
            $fee = DestinationFee::with('destination.media')->find($item->reference_id);
            if (!$fee || !$fee->destination) {
                return null;
            }

            $cover = $fee->destination->media->firstWhere('is_cover', true)
                ?? $fee->destination->media->first();

            return $cover?->file_path;
        }

        return null;
    }

    /**
     * Ensure current user can access this itinerary.
     */
    private function authorizeAccess(Itinerary $itinerary): void
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            return;
        }

        if ($itinerary->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this itinerary.');
        }
    }
}
