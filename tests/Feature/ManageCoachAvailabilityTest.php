<?php

namespace Tests\Feature;

use App\DataTransferObjects\CoachAvailabilityDTO;
use App\DataTransferObjects\UserDTO;
use App\Http\Middleware\FirebaseAuth;
use App\Repositories\CoachAvailabilityRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ManageCoachAvailabilityTest extends TestCase
{
    private const FIREBASE_UID = 'test-coach-firebase-uid';

    private const COACH_ID = '11111111111111111111111111111111';

    private const SLOT_ID = '00000000000000000000000000000006';

    /**
     * create a fake active Coach and simulate firebase authentication
     */
    private function mockActiveCoach(
        string $role = 'COACH',
        string $status = 'ACTIVE',
    ): UserDTO {
        // Simulate Firebase verifying the signed-in user.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        self::FIREBASE_UID
                    );

                    return $next($request);
                });
        });

        // create a coach profile without inserting anything into the database
        $coach = new UserDTO(
            id: self::COACH_ID,
            firebaseUid: self::FIREBASE_UID,
            studentNumber: null,
            fullName: 'Test Coach',
            email: 'coach@example.com',
            role: $role,
            status: $status,
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // return the fake coach when the controller looks up the logged in user
        $this->mock(UserRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with(self::FIREBASE_UID)
                ->andReturn($coach);
        });

        // these management actions do not need to load all active time slots
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldNotReceive('listActive');
        });

        return $coach;
    }

    public function test_active_coach_can_view_availability_for_a_date(): void
    {
        $coach = $this->mockActiveCoach();
        $date = now()->addDay()->format('Y-m-d');

        // simulate an availability row returned by SQL Connect
        $availability = new CoachAvailabilityDTO(
            id: $coach->id.':'.$date.':'.self::SLOT_ID,
            availabilityDate: $date,
            createdAt: now()->toIso8601String(),
            coach: [
                'id' => $coach->id,
                'fullName' => $coach->fullName,
            ],
            timeSlot: [
                'id' => self::SLOT_ID,
                'startTime' => '10:00',
                'endTime' => '11:00',
            ],
        );

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($coach, $date, $availability) {
                $mock->shouldReceive('getForCoachAndDate')
                    ->once()
                    ->with($coach->id, $date)
                    ->andReturn([$availability]);
            }
        );

        // A supplied coach ID must not override the signed-in Coach.
        $response = $this->getJson(
            "/api/coach/availability?availability_date={$date}"
            .'&coach_id=22222222222222222222222222222222'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath(
            'data.0.availability_date',
            $date
        );
        $response->assertJsonPath(
            'data.0.time_slot.id',
            self::SLOT_ID
        );
        $response->assertJsonPath(
            'data.0.time_slot.startTime',
            '10:00'
        );
    }

    public function test_coach_without_availability_receives_an_empty_list(): void
    {
        $coach = $this->mockActiveCoach();
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($coach, $date) {
                $mock->shouldReceive('getForCoachAndDate')
                    ->once()
                    ->with($coach->id, $date)
                    ->andReturn([]);
            }
        );

        $response = $this->getJson(
            "/api/coach/availability?availability_date={$date}"
        );

        $response->assertStatus(200);
        $response->assertExactJson(['data' => []]);
    }

    public function test_nonexistent_availability_cannot_be_removed(): void
    {
        $coach = $this->mockActiveCoach();
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($coach, $date) {
                // simulate that this Coach never published the selected slot
                $mock->shouldReceive('isCoachAvailable')
                    ->once()
                    ->with($date, self::SLOT_ID, $coach->id)
                    ->andReturn(false);

                // there is no availability, so no booking check or deletion occurs
                $mock->shouldNotReceive('hasConfirmedBooking');
                $mock->shouldNotReceive('deleteForCoachAndSlot');
            }
        );

        $response = $this->deleteJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_id' => self::SLOT_ID,
        ]);

        $response->assertStatus(404);
        $response->assertExactJson([
            'message' => 'Availability not found.',
        ]);
    }

    public function test_confirmed_booking_blocks_availability_removal(): void
    {
        $coach = $this->mockActiveCoach();
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($coach, $date) {
                // the selected availability exists
                $mock->shouldReceive('isCoachAvailable')
                    ->once()
                    ->with($date, self::SLOT_ID, $coach->id)
                    ->andReturn(true);

                // A confirmed coaching booking already uses this slot
                $mock->shouldReceive('hasConfirmedBooking')
                    ->once()
                    ->with($coach->id, $date, self::SLOT_ID)
                    ->andReturn(true);

                // the existing booking means the availability must remain
                $mock->shouldNotReceive('deleteForCoachAndSlot');
            }
        );

        $response = $this->deleteJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_id' => self::SLOT_ID,
        ]);

        $response->assertStatus(409);
        $response->assertExactJson([
            'message' => 'This time slot has a confirmed coaching booking.',
        ]);
    }

    public function test_unbooked_availability_can_be_removed(): void
    {
        $coach = $this->mockActiveCoach();
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($coach, $date) {
                // the coach published this availability
                $mock->shouldReceive('isCoachAvailable')
                    ->once()
                    ->with($date, self::SLOT_ID, $coach->id)
                    ->andReturn(true);

                // no confirmed coaching booking uses it
                $mock->shouldReceive('hasConfirmedBooking')
                    ->once()
                    ->with($coach->id, $date, self::SLOT_ID)
                    ->andReturn(false);

                // the exact coach/date/slot record must be deleted once
                $mock->shouldReceive('deleteForCoachAndSlot')
                    ->once()
                    ->with($coach->id, self::SLOT_ID, $date)
                    ->andReturnNull();
            }
        );

        $response = $this->deleteJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_id' => self::SLOT_ID,
            'coach_id' => '22222222222222222222222222222222',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Coach availability removed successfully.',
        ]);
    }

    #[DataProvider('profilesDeniedRemoval')]
    public function test_unauthorised_profile_cannot_remove_availability(
        string $role,
        string $status,
    ): void {
        $this->mockActiveCoach($role, $status);
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            // reject the profile before checking or changing availability
            $mock->shouldNotReceive('isCoachAvailable');
            $mock->shouldNotReceive('hasConfirmedBooking');
            $mock->shouldNotReceive('deleteForCoachAndSlot');
        });

        $response = $this->deleteJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_id' => self::SLOT_ID,
        ]);

        $response->assertStatus(403);
        $response->assertExactJson([
            'message' => 'Only active Coaches may remove availability.',
        ]);
    }

    #[DataProvider('profilesDeniedRemoval')]
    public function test_unauthorised_profile_cannot_view_availability(
        string $role,
        string $status,
    ): void {
        $this->mockActiveCoach($role, $status);
        $date = now()->addDay()->format('Y-m-d');

        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            // reject the profile before loading any Coach availability
            $mock->shouldNotReceive('getForCoachAndDate');
        });

        $response = $this->getJson(
            "/api/coach/availability?availability_date={$date}"
        );

        $response->assertStatus(403);
        $response->assertExactJson([
            'message' => 'Only active Coaches may view their availability.',
        ]);
    }

    public static function profilesDeniedRemoval(): array
    {
        return [
            'ordinary user' => ['USER', 'ACTIVE'],
            'inactive coach' => ['COACH', 'INACTIVE'],
        ];
    }
}
