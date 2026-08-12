<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = Booking::query()
            ->with(['user', 'court'])
            ->orderByDesc('createdAt')
            ->get();

        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'userId' => ['required', Rule::exists((new User())->getTable(), 'id')],
            'courtId' => ['required', Rule::exists((new Court())->getTable(), 'id')],
            'startTime' => ['required', 'date'],
            'endTime' => ['required', 'date', 'after:startTime'],
            'status' => ['nullable', 'in:CONFIRMED,CANCELLED,COMPLETED'],
        ]);

        $booking = Booking::query()->create([
            'userId' => $validated['userId'],
            'courtId' => $validated['courtId'],
            'startTime' => $validated['startTime'],
            'endTime' => $validated['endTime'],
            'status' => $validated['status'] ?? 'CONFIRMED',
        ]);

        return response()->json($booking->load(['user', 'court']), 201);
    }

    public function show(string $id): JsonResponse
    {
        $booking = Booking::query()
            ->with(['user', 'court'])
            ->findOrFail($id);

        return response()->json($booking);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $booking = Booking::query()->findOrFail($id);

        $validated = $request->validate([
            'userId' => ['sometimes', 'required', Rule::exists((new User())->getTable(), 'id')],
            'courtId' => ['sometimes', 'required', Rule::exists((new Court())->getTable(), 'id')],
            'startTime' => ['sometimes', 'required', 'date'],
            'endTime' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'in:CONFIRMED,CANCELLED,COMPLETED'],
        ]);

        if (isset($validated['startTime'], $validated['endTime'])
            && strtotime((string) $validated['endTime']) <= strtotime((string) $validated['startTime'])) {
            return response()->json([
                'message' => 'The endTime must be a date after startTime.',
            ], 422);
        }

        $booking->fill($validated);
        $booking->save();

        return response()->json($booking->load(['user', 'court']));
    }

    public function destroy(string $id): JsonResponse
    {
        $booking = Booking::query()->findOrFail($id);
        $booking->delete();

        return response()->noContent();
    }
}

    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     //
    // }
    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }
    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }
    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     //
    // }
    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(string $id)
    // {
    //     //
    // }
    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }
    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //