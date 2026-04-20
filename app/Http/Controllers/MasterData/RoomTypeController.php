<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Hotel;
use App\Models\MasterData\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index(Hotel $hotel): JsonResponse
    {
        return response()->json($hotel->roomTypes);
    }

    public function store(Request $request, Hotel $hotel): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:single,double,triple'],
        ]);

        $data['hotel_id'] = $hotel->id;
        $roomType = RoomType::create($data);

        return response()->json($roomType, 201);
    }

    public function destroy(Hotel $hotel, RoomType $roomType): JsonResponse
    {
        if ($roomType->hotel_id !== $hotel->id) {
            abort(403, 'Unauthorized.');
        }

        $roomType->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
