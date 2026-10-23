<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = CarbonImmutable::today();

        $activeBookings = DB::table('bookings')
            ->where('status', 'CONFIRMED')
            ->where('booking_date', '>=', $today->toDateString())
            ->count();

        $coachCount = DB::table('users')
            ->where('role', 'COACH')
            ->count();

        $playerCount = DB::table('users')
            ->where('role', 'USER')
            ->count();

        return response()->json([
            ['label' => 'Active bookings', 'value' => $activeBookings],
            ['label' => 'Coaches registered', 'value' => $coachCount],
            ['label' => 'Players registered', 'value' => $playerCount],
        ]);
    }

    public function utilisation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d|after_or_equal:from',
            'courts' => 'sometimes|string',
        ]);

        $from = CarbonImmutable::createFromFormat('Y-m-d', $validated['from'])->startOfDay();
        $to = CarbonImmutable::createFromFormat('Y-m-d', $validated['to'])->startOfDay();

        if ($to->diffInDays($from) > 93) {
            return response()->json([
                'message' => 'Date range is too large. Please select a range of 93 days or fewer.',
            ], 422);
        }

        $courtIds = [];
        if (array_key_exists('courts', $validated) && is_string($validated['courts'])) {
            $courtIds = array_values(array_filter(array_map('trim', explode(',', $validated['courts']))));
        }

        $bookingsQuery = DB::table('bookings')
            ->select(
                'booking_date',
                DB::raw("COUNT(DISTINCT CONCAT(court_id, '-', time_slot_id)) as booked_cells")
            )
            ->where('bookings.status', 'CONFIRMED')
            ->whereBetween('booking_date', [$from->toDateString(), $to->toDateString()]);

        if (! empty($courtIds)) {
            $bookingsQuery->whereIn('court_id', $courtIds);
        } else {
            $bookingsQuery
                ->join('courts', 'courts.id', '=', 'bookings.court_id')
                ->where('courts.status', 'ACTIVE');
        }

        $rows = $bookingsQuery
            ->groupBy('booking_date')
            ->orderBy('booking_date')
            ->get();

        $bookedByDate = $rows->mapWithKeys(fn ($row) => [(string) $row->booking_date => (int) $row->booked_cells]);

        $courtCount = empty($courtIds)
            ? DB::table('courts')->where('status', 'ACTIVE')->count()
            : DB::table('courts')->whereIn('id', $courtIds)->count();

        $activeSlots = DB::table('time_slots')
            ->where('is_active', true)
            ->count();

        $capacity = $courtCount * $activeSlots;

        $series = [];
        for ($cursor = $from; $cursor->lte($to); $cursor = $cursor->addDay()) {
            $date = $cursor->toDateString();
            $booked = (int) ($bookedByDate[$date] ?? 0);

            $series[] = [
                'date' => $date,
                'bookedCells' => $booked,
                'capacityCells' => $capacity,
                'utilisation' => $capacity > 0 ? round($booked / $capacity, 4) : 0,
            ];
        }

        return response()->json([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'series' => $series,
        ]);
    }
}
