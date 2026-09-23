<?php

namespace Tests\Feature;

use App\DataTransferObjects\CourtDTO;
use App\DataTransferObjects\UserDTO;
use App\Http\Middleware\FirebaseAuth;
use App\Repositories\BookingPlayerRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CoachAvailabilityRepository;
use App\Repositories\CourtRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;


class CoachBookingConflictTest extends TestCase
{
    #[DataProvider('coachBookingCases')]
    public function test_coach_booking_conflict_check(
        bool $coachAlreadyBooked,
        int $expectedStatus,
    ): void
    {
        $studentId = str_repeat('1', 32);
        $coachId = str_repeat('2', 32);
        $courtId = str_repeat('3', 32);
        $slotId = str_repeat('4', 32);
        $bookingId = str_repeat('5', 32);

        $date = now('Africa/Johannesburg')->addDay()->format('Y-m-d');
        $timestamp = now()->toIso8601String();

        // Simulate authentication without contacting Firebase.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'test-student');

                    return $next($request);
                });
        });

        $student = new UserDTO(
            id: $studentId,
            firebaseUid: 'test-student',
            studentNumber: '12345678',
            fullName: 'Test Student',
            email: 'student@example.com',
            role: 'USER',
            status: 'ACTIVE',
            createdAt: $timestamp,
            updatedAt: $timestamp,
        );

        $this->mock(UserRepository::class, function ($mock) use ($student) {
            $mock->shouldReceive('findByFirebaseUid')
                ->with('test-student')
                ->andReturn($student);
        });

        $court = new CourtDTO(
            id: $courtId,
            name: 'Another Court',
            description: null,
            status: 'ACTIVE',
            createdAt: $timestamp,
            updatedAt: $timestamp,
        );

        $this->mock(CourtRepository::class, function ($mock) use ($courtId, $court) {
            $mock->shouldReceive('findById')
                ->with($courtId)
                ->andReturn($court);
        });

        $this->mock(TimeSlotRepository::class, function ($mock) use ($slotId) {
            $mock->shouldReceive('listActive')->andReturn([
                (object) [
                    'id' => $slotId,
                    'startTime' => '10:00',
                    'endTime' => '11:00',
                ],
            ]);
        });

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($date, $slotId, $coachId, $coachAlreadyBooked) {
                // The coach published availability for this slot.
                $mock->shouldReceive('isCoachAvailable')
                    ->with($date, $slotId, $coachId)
                    ->andReturn(true);

                // Simulate whether another booking already uses this coach's slot.
                $mock->shouldReceive('hasConfirmedBooking')
                    ->once()
                    ->with($coachId, $date, $slotId)
                    ->andReturn($coachAlreadyBooked);
            }
        );

        $bookingCreated = false;

        $this->mock(
            BookingRepository::class,
            function ($mock) use (
                $studentId,
                $courtId,
                $date,
                $slotId,
                $bookingId,
                &$bookingCreated,
            ) {
                // This student is free and the other court is free.
                $mock->shouldReceive('userHasBookingForSlot')
                    ->with($studentId, $date, $slotId)
                    ->andReturn(false);

                $mock->shouldReceive('isSlotTaken')
                    ->with($courtId, $date, $slotId)
                    ->andReturn(false);

                // Record any attempted creation without writing to the database.
                $mock->shouldReceive('create')
                    ->andReturnUsing(function ($params) use (
                        $bookingId,
                        &$bookingCreated,
                    ) {
                        $bookingCreated = true;

                        return ['booking_insert' => ['id' => $bookingId]];
                    });
            }
        );

        $this->mock(
            BookingPlayerRepository::class,
            function ($mock) use ($bookingId, $studentId) {
                $mock->shouldReceive('add')
                    ->with($bookingId, $studentId, 1)
                    ->andReturnNull();
            }
        );

        $response = $this->postJson('/api/bookings', [
            'court_id' => $courtId,
            'time_slot_id' => $slotId,
            'booking_date' => $date,
            'booking_type' => 'PRIVATE_COACH',
            'coach_id' => $coachId,
        ]);

        $response->assertStatus($expectedStatus);

        if ($coachAlreadyBooked) {
            $response->assertJsonPath('code', 'COACH_ALREADY_BOOKED');
            $this->assertFalse(
                $bookingCreated,
                'A booking must not be created when the coach is already booked.'
            );
        } else {
            $response->assertJsonPath('id', $bookingId);
            $this->assertTrue(
                $bookingCreated,
                'A booking should be created when the coach and court are free.'
            );
        }
    }
    
    public static function coachBookingCases(): array
    {
        return [
            'coach already booked' => [true, 409],
            'coach is free' => [false, 201],
        ];
    }
}