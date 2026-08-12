<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourtController extends Controller
{
    public function index(): JsonResponse
    {
        $courts = Court::query()
            ->orderBy('name')
            ->get();

        return response()->json($courts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('Court', 'name')],
            'status' => ['nullable', 'in:AVAILABLE,UNAVAILABLE,MAINTENANCE'],
        ]);

        $court = Court::query()->create([
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'AVAILABLE',
        ]);

        return response()->json($court, 201);
    }

    public function show(string $id): JsonResponse
    {
        $court = Court::query()->findOrFail($id);

        return response()->json($court);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $court = Court::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('Court', 'name')->ignore($court->id),
            ],
            'status' => ['sometimes', 'required', 'in:AVAILABLE,UNAVAILABLE,MAINTENANCE'],
        ]);

        $court->fill($validated);
        $court->save();

        return response()->json($court);
    }

    public function destroy(string $id): JsonResponse
    {
        $court = Court::query()->findOrFail($id);
        $court->delete();

        return response()->json(status: 204);
    }
}
