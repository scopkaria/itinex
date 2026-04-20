<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\TenantScoped;
use App\Models\MasterData\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    use TenantScoped;

    public function index(Request $request): JsonResponse
    {
        $destinations = Destination::where('company_id', $this->companyId($request))
            ->orderBy('name')
            ->get();

        return response()->json($destinations);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ]);

        $data['company_id'] = $this->companyId($request);
        $destination = Destination::create($data);

        return response()->json($destination, 201);
    }

    public function show(Request $request, Destination $destination): JsonResponse
    {
        $this->authorizeCompany($request, $destination);
        return response()->json($destination->load('hotels'));
    }

    public function update(Request $request, Destination $destination): JsonResponse
    {
        $this->authorizeCompany($request, $destination);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ]);

        $destination->update($data);

        return response()->json($destination);
    }

    public function destroy(Request $request, Destination $destination): JsonResponse
    {
        $this->authorizeCompany($request, $destination);
        $destination->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }

    private function authorizeCompany(Request $request, Destination $destination): void
    {
        if ($destination->company_id !== $this->companyId($request)) {
            abort(403, 'Unauthorized.');
        }
    }
}
