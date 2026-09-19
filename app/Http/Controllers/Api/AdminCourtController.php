<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CourtRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCourtController extends Controller
{
    public function __construct(
        private CourtRepository $courts,
    ) {}

    public function index(): JsonResponse
    {
        $courts = $this->courts->listAll();

        return response()->json(
            array_map(fn ($c) => $c->toArray(), $courts),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'sometimes|string|max:255',
            'status'   => 'sometimes|in:ACTIVE,INACTIVE',
        ]);

        $this->courts->create($validated);

        return response()->json(['message' => 'Court created successfully.'], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $court = $this->courts->findById($id);

        if (! $court) {
            return response()->json(['message' => 'Court not found.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $this->courts->updateStatus($id, $validated['status']);

        $court = $this->courts->findById($id);

        return response()->json($court?->toArray());
    }

    public function destroy(string $id): JsonResponse
    {
        $court = $this->courts->findById($id);

        if (! $court) {
            return response()->json(['message' => 'Court not found.'], 404);
        }

        $this->courts->updateStatus($id, 'INACTIVE');

        return response()->json(['message' => 'Court deactivated successfully.']);
    }
}