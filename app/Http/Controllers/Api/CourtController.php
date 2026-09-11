<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CourtRepository;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    public function __construct(
        private CourtRepository $courts,
    ) {}

    /**
     * GET /api/courts
     */
    public function index(): JsonResponse
    {
        $courts = $this->courts->listActive();

        return response()->json(
            array_map(fn ($c) => $c->toArray(), $courts),
        );
    }

    /**
     * GET /api/courts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $court = $this->courts->findById($id);

        if (! $court) {
            return response()->json(['message' => 'Court not found.'], 404);
        }

        return response()->json($court->toArray());
    }
}
