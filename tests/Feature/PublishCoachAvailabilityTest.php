<?php

namespace Tests\Feature;

use App\DataTransferObjects\TimeSlotDTO;
use App\DataTransferObjects\UserDTO;
use App\Http\Middleware\FirebaseAuth;
use App\Repositories\CoachAvailabilityRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Tests\TestCase;

class PublishCoachAvailabilityTest extends TestCase
{
    public function test_user_without_application_profile_cannot_publish(): void
    {
        // Pretend Firebase has verified this identity.
        // This replacement is used only inside this test.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Pretend the database has no profile for this Firebase user.
        $this->mock(UserRepository::class, function ($mock) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn(null);
        });

        // Without a profile, the controller must stop before loading slots.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldNotReceive('listActive');
        });

        // It must not check or save availability either.
        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            $mock->shouldNotReceive('isCoachAvailable');
            $mock->shouldNotReceive('create');
        });

        // Send valid input to the real application route.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        // Confirm that the controller rejects the missing profile.
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'User profile not found.',
        ]);
    }

    public function test_normal_user_cannot_publish_availability(): void
    {
        // Simulate a successful Firebase login.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Create a fake, active USER profile, not a COACH.
        // Nothing is inserted into the real database.
        $user = new UserDTO(
            id: '00000000-0000-4000-8000-000000000002',
            firebaseUid: 'test-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: 'USER',
            status: 'ACTIVE',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // Return that profile when the controller looks up the user.
        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn($user);
        });

        // A normal USER must be rejected before any slot lookup or saving.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldNotReceive('listActive');
        });

        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            $mock->shouldNotReceive('isCoachAvailable');
            $mock->shouldNotReceive('create');
        });

        // Submit correctly formatted availability.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        // Login is valid, but the user's role does not grant permission.
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Only active Coaches may publish availability.',
        ]);
    }

    public function test_inactive_coach_cannot_publish_availability(): void
    {
        // Simulate a successful Firebase login.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Create a fake, inactive COACH profile.
        // Nothing is inserted into the real database.
        $user = new UserDTO(
            id: '00000000-0000-4000-8000-000000000002',
            firebaseUid: 'test-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: 'COACH',
            status: 'INACTIVE',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // Return that profile when the controller looks up the user.
        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn($user);
        });

        // An inactive Coach must be rejected before any slot lookup or saving.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldNotReceive('listActive');
        });

        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            $mock->shouldNotReceive('isCoachAvailable');
            $mock->shouldNotReceive('create');
        });

        // Submit correctly formatted availability.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        // The Coach's account is inactive, so access is forbidden.
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Only active Coaches may publish availability.',
        ]);
    }

    public function test_coach_cannot_publish_an_invalid_or_inactive_slot(): void
    {
        // Simulate a successful Firebase login.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Create a fake, active COACH profile.
        // Nothing is inserted into the real database.
        $user = new UserDTO(
            id: '00000000-0000-4000-8000-000000000002',
            firebaseUid: 'test-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // Return that profile when the controller looks up the user.
        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn($user);
        });

        // Simulate no active time slots in the database.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldReceive('listActive')
                ->once()
                ->andReturn([]);
        });

        $this->mock(CoachAvailabilityRepository::class, function ($mock) {
            $mock->shouldNotReceive('isCoachAvailable');
            $mock->shouldNotReceive('create');
        });

        // Submit correctly formatted availability.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        // The selected slot is not active or does not exist.
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'One or more selected time slots are invalid or inactive.',
            'invalid_time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);
    }

    public function test_active_coach_can_publish_availability(): void
    {
        // Simulate a successful Firebase login.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Create a fake, active COACH profile.
        // Nothing is inserted into the real database.
        $user = new UserDTO(
            id: '00000000-0000-4000-8000-000000000002',
            firebaseUid: 'test-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // Return that profile when the controller looks up the user.
        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn($user);
        });

        // Return a real-looking active slot, without querying the database.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldReceive('listActive')
                ->once()
                ->andReturn([
                    new TimeSlotDTO(
                        id: '00000000000000000000000000000006',
                        startTime: '08:00',
                        endTime: '09:00',
                        displayOrder: 1,
                        isActive: true,
                    ),
                ]);
        });

        $date = now()->addDay()->format('Y-m-d');

        // Availability does not exist yet, so saving must happen exactly once.
        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($user, $date) {
                $slotId = '00000000000000000000000000000006';

                $mock->shouldReceive('isCoachAvailable')
                    ->once()
                    ->with($date, $slotId, $user->id)
                    ->andReturn(false);

                $mock->shouldReceive('create')
                    ->once()
                    ->with($user->id, $slotId, $date)
                    ->andReturnNull();
            }
        );

        // Submit correctly formatted availability.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Coach availability published successfully.',
            'data' => [
                'availability_date' => $date,
                'time_slot_ids' => [
                    '00000000000000000000000000000006',
                ],
                'created_time_slot_ids' => [
                    '00000000000000000000000000000006',
                ],
                'existing_time_slot_ids' => [],
            ],
        ]);
    }

    public function test_existing_availability_is_not_created_again(): void
    {
        // Simulate a successful Firebase login.
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-firebase-uid'
                    );

                    return $next($request);
                });
        });

        // Create a fake, active COACH profile.
        // Nothing is inserted into the real database.
        $user = new UserDTO(
            id: '00000000-0000-4000-8000-000000000002',
            firebaseUid: 'test-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: now()->toIso8601String(),
            updatedAt: now()->toIso8601String(),
        );

        // Return that profile when the controller looks up the user.
        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-firebase-uid')
                ->andReturn($user);
        });

        // Return a real-looking active slot, without querying the database.
        $this->mock(TimeSlotRepository::class, function ($mock) {
            $mock->shouldReceive('listActive')
                ->once()
                ->andReturn([
                    new TimeSlotDTO(
                        id: '00000000000000000000000000000006',
                        startTime: '08:00',
                        endTime: '09:00',
                        displayOrder: 1,
                        isActive: true,
                    ),
                ]);
        });

        $date = now()->addDay()->format('Y-m-d');

        // Existing availability must not be saved again.
        $this->mock(
            CoachAvailabilityRepository::class,
            function ($mock) use ($user, $date) {
                $slotId = '00000000000000000000000000000006';

                // Simulate an availability record that already exists.
                $mock->shouldReceive('isCoachAvailable')
                    ->once()
                    ->with($date, $slotId, $user->id)
                    ->andReturn(true);

                // The controller must skip saving this record again.
                $mock->shouldNotReceive('create');
            }
        );

        // Submit correctly formatted availability.
        $response = $this->putJson('/api/coach/availability', [
            'availability_date' => $date,
            'time_slot_ids' => [
                '00000000000000000000000000000006',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Coach availability published successfully.',
            'data' => [
                'availability_date' => $date,
                'time_slot_ids' => [
                    '00000000000000000000000000000006',
                ],
                'created_time_slot_ids' => [],
                'existing_time_slot_ids' => [
                    '00000000000000000000000000000006',
                ],
            ],
        ]);
    }
}
