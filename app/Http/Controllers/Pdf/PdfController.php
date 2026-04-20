<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Itinerary\Itinerary;
use App\Models\ItineraryTemplate;
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

        return [
            'itinerary' => $itinerary,
            'company' => $company,
            'template' => $template,
            'costSheet' => $costSheet,
        ];
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
