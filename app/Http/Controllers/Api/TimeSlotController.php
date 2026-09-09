<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\TimeSlotRepository;
use Illuminate\Http\JsonResponse;

class TimeSlotController extends Controller
{
    public function __construct(
        private TimeSlotRepository $timeSlots,
    ) {}

    /**
     * GET /api/time-slots
     */
    public function index(): JsonResponse
    {
        $slots = $this->timeSlots->listActive();

        return response()->json(
            array_map(fn ($s) => $s->toArray(), $slots),
        );
    }
}
