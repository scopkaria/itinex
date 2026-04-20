<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\MealPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(MealPlan::all());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'in:BB,HB,FB,AI'],
        ]);

        $mealPlan = MealPlan::create($data);

        return response()->json($mealPlan, 201);
    }

    public function destroy(MealPlan $mealPlan): JsonResponse
    {
        $mealPlan->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
