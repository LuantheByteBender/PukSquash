<?php

namespace Tests\Feature;

use App\DataTransferObjects\BookingDTO;
use App\DataTransferObjects\UserDTO;
use App\Http\Middleware\FirebaseAuth;
use App\Repositories\BookingRepository;
use App\Repositories\UserRepository;
use App\Services\FirebaseAuthService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class UpcomingCoachBookingsTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_coach_sees_ongoing_and_future_sessions_until_they_end(): void
    {
        // 10:00 UTC is 12:00 in SAST
        CarbonImmutable::setTestNow(
            CarbonImmutable::parse('2026-09-15 10:00:00', 'UTC')
        );

        // simulate firebase verifying the signed-in Coach
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-coach-firebase-uid'
                    );

                    return $next($request);
                });
        });

        $coach = new UserDTO(
            id: '20000000000000000000000000000001',
            firebaseUid: 'test-coach-firebase-uid',
            studentNumber: null,
            fullName: 'Test Coach',
            email: 'coach@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $this->mock(UserRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-firebase-uid')
                ->andReturn($coach);
        });

        $yesterday = $this->booking(
            'yesterday', '2026-09-14', '14:00:00', '15:00:00'
        );

        $ended = $this->booking(
            'ended', '2026-09-15', '10:00:00', '11:00:00'
        );

        $endsExactlyNow = $this->booking(
            'ends-now', '2026-09-15', '11:00:00', '12:00:00'
        );

        $ongoing = $this->booking(
            'ongoing', '2026-09-15', '12:00:00', '13:00:00'
        );

        $laterToday = $this->booking(
            'later-today', '2026-09-15', '14:00:00', '15:00:00'
        );

        $tomorrow = $this->booking(
            'tomorrow', '2026-09-16', '09:00:00', '10:00:00'
        );

        $this->mock(BookingRepository::class, function ($mock) use (
            $coach,
            $yesterday,
            $ended,
            $endsExactlyNow,
            $ongoing,
            $laterToday,
            $tomorrow,
        ) {
            $mock->shouldReceive('getConfirmedByCoach')
                ->once()
                ->with($coach->id)
                ->andReturn([
                    $tomorrow,
                    $ended,
                    $laterToday,
                    $endsExactlyNow,
                    $yesterday,
                    $ongoing,
                ]);
        });

        // supplying another Coach ID must not change whose bookings are loaded
        $response = $this->getJson(
            '/api/coach/bookings/upcoming?coach_id=another-coach'
        );

        $response->assertOk();
        $response->assertJsonCount(3);

        // ended sessions are excluded and remaining sessions are ordered
        $this->assertSame(
            ['ongoing', 'later-today', 'tomorrow'],
            array_column($response->json(), 'id'),
        );

        // check the information needed by the frontend table
        $this->assertSame([
            'id' => 'ongoing',
            'player' => [
                'id' => '30000000000000000000000000000001',
                'name' => 'Test Student',
            ],
            'booking_date' => '2026-09-15',
            'time_slot' => [
                'id' => '00000000000000000000000000000006',
                'start_time' => '12:00:00',
                'end_time' => '13:00:00',
            ],
            'court' => [
                'id' => '40000000000000000000000000000001',
                'name' => 'Court 1',
            ],
            'status' => 'CONFIRMED',
        ], $response->json('0'));
    }

    public function test_normal_user_cannot_view_coach_bookings(): void
    {
        $this->assertProfileCannotViewBookings('USER', 'ACTIVE');
    }

    public function test_inactive_coach_cannot_view_coach_bookings(): void
    {
        $this->assertProfileCannotViewBookings('COACH', 'INACTIVE');
    }

    public function test_missing_profile_cannot_view_coach_bookings(): void
    {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-coach-firebase-uid'
                    );

                    return $next($request);
                });
        });

        $this->mock(UserRepository::class, function ($mock) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-firebase-uid')
                ->andReturn(null);
        });

        $this->mock(BookingRepository::class, function ($mock) {
            $mock->shouldNotReceive('getConfirmedByCoach');
        });

        $response = $this->getJson('/api/coach/bookings/upcoming');

        $response->assertStatus(404);
        $response->assertExactJson([
            'message' => 'User profile not found.',
        ]);
    }

    public function test_request_without_token_cannot_view_coach_bookings(): void
    {
        // use the real middleware, but prevent loading live firebase credentials
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldNotReceive('verifyToken');
            $mock->shouldNotReceive('getUser');
        });

        $this->mock(UserRepository::class, function ($mock) {
            $mock->shouldNotReceive('findByFirebaseUid');
        });

        $this->mock(BookingRepository::class, function ($mock) {
            $mock->shouldNotReceive('getConfirmedByCoach');
        });

        $response = $this->getJson('/api/coach/bookings/upcoming');

        $response->assertStatus(401);
        $response->assertExactJson([
            'message' => 'Authentication token missing.',
        ]);
    }

    public function test_coach_without_bookings_receives_an_empty_list(): void
    {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-coach-firebase-uid'
                    );

                    return $next($request);
                });
        });

        $coach = new UserDTO(
            id: '20000000000000000000000000000001',
            firebaseUid: 'test-coach-firebase-uid',
            studentNumber: null,
            fullName: 'Test Coach',
            email: 'coach@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $this->mock(UserRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-firebase-uid')
                ->andReturn($coach);
        });

        $this->mock(BookingRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('getConfirmedByCoach')
                ->once()
                ->with($coach->id)
                ->andReturn([]);
        });

        $response = $this->getJson('/api/coach/bookings/upcoming');

        $response->assertOk();
        $response->assertExactJson([]);
    }

    /**
     * check that a rejected profile never triggers a booking lookup
     */
    private function assertProfileCannotViewBookings(
        string $role,
        string $status,
    ): void {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set(
                        'firebase_uid',
                        'test-coach-firebase-uid'
                    );

                    return $next($request);
                });
        });

        $user = new UserDTO(
            id: '20000000000000000000000000000001',
            firebaseUid: 'test-coach-firebase-uid',
            studentNumber: null,
            fullName: 'Test User',
            email: 'user@example.com',
            role: $role,
            status: $status,
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-firebase-uid')
                ->andReturn($user);
        });

        $this->mock(BookingRepository::class, function ($mock) {
            $mock->shouldNotReceive('getConfirmedByCoach');
        });

        $response = $this->getJson('/api/coach/bookings/upcoming');

        $response->assertStatus(403);
        $response->assertExactJson([
            'message' => 'Only active Coaches may view coaching bookings.',
        ]);
    }

    /**
     * create a booking fixture without accessing the live database
     */
    private function booking(
        string $id,
        string $date,
        string $startTime,
        string $endTime,
    ): BookingDTO {
        return new BookingDTO(
            id: $id,
            bookingDate: $date,
            bookingType: 'PRIVATE_COACH',
            status: 'CONFIRMED',
            bookedAt: '2026-09-01T08:00:00Z',
            cancelledAt: null,
            createdAt: '2026-09-01T08:00:00Z',
            court: [
                'id' => '40000000000000000000000000000001',
                'name' => 'Court 1',
            ],
            timeSlot: [
                'id' => '00000000000000000000000000000006',
                'startTime' => $startTime,
                'endTime' => $endTime,
            ],
            createdBy: [
                'id' => '30000000000000000000000000000001',
                'fullName' => 'Test Student',
            ],
            coach: [
                'id' => '20000000000000000000000000000001',
                'fullName' => 'Test Coach',
            ],
            players: [],
        );
    }
}
