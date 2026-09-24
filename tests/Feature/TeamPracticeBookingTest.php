<?php

namespace Tests\Feature;

use App\DataTransferObjects\CourtDTO;
use App\DataTransferObjects\TimeSlotDTO;
use App\DataTransferObjects\UserDTO;
use App\Http\Middleware\FirebaseAuth;
use App\Repositories\AvailabilityRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TeamPracticeBookingTest extends TestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_availability_requires_active_coach(): void
    {
        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'test-uid');
                    return $next($request);
                });
        });

        $user = new UserDTO(
            id: '20000000000000000000000000000001',
            firebaseUid: 'test-uid',
            studentNumber: null,
            fullName: 'Student',
            email: 'student@example.com',
            role: 'STUDENT',
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $this->mock(UserRepository::class, function ($mock) use ($user) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-uid')
                ->andReturn($user);
        });

        $this->mock(AvailabilityRepository::class);
        $this->mock(BookingRepository::class);
        $this->mock(CourtRepository::class);
        $this->mock(TimeSlotRepository::class);

        $res = $this->getJson('/api/coach/team-practice/availability?date=2026-09-02');

        $res->assertStatus(403);
    }

    public function test_store_returns_conflict_when_any_selected_cell_is_taken(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-01 09:00:00', 'Africa/Johannesburg'));

        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'test-coach-uid');
                    return $next($request);
                });
        });

        $coach = new UserDTO(
            id: '20000000000000000000000000000002',
            firebaseUid: 'test-coach-uid',
            studentNumber: null,
            fullName: 'Coach',
            email: 'coach@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $court = new CourtDTO(
            id: '30000000000000000000000000000001',
            name: 'Court 1',
            description: null,
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $slot = new TimeSlotDTO(
            id: '40000000000000000000000000000001',
            startTime: '08:00:00',
            endTime: '09:00:00',
            displayOrder: 1,
            isActive: true,
        );

        $this->mock(UserRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-uid')
                ->andReturn($coach);
        });

        $this->mock(AvailabilityRepository::class);

        $this->mock(TimeSlotRepository::class, function ($mock) use ($slot) {
            $mock->shouldReceive('listActive')
                ->once()
                ->andReturn([$slot]);
        });

        $this->mock(CourtRepository::class, function ($mock) use ($court) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($court->id)
                ->andReturn($court);
        });

        $this->mock(BookingRepository::class, function ($mock) use ($court, $slot) {
            $mock->shouldReceive('isSlotTaken')
                ->once()
                ->with($court->id, '2026-09-02', $slot->id)
                ->andReturn(true);
        });

        $res = $this->postJson('/api/coach/team-practices', [
            'selections' => [
                [
                    'court_id' => $court->id,
                    'time_slot_id' => $slot->id,
                    'booking_date' => '2026-09-02',
                ],
            ],
        ]);

        $res->assertStatus(409);
        $res->assertJsonPath('code', 'SLOT_TAKEN');
    }

    public function test_store_creates_multiple_team_practice_bookings(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-01 09:00:00', 'Africa/Johannesburg'));

        $this->mock(FirebaseAuth::class, function ($mock) {
            $mock->shouldReceive('handle')
                ->once()
                ->andReturnUsing(function ($request, $next) {
                    $request->attributes->set('firebase_uid', 'test-coach-uid');
                    return $next($request);
                });
        });

        $coach = new UserDTO(
            id: '20000000000000000000000000000003',
            firebaseUid: 'test-coach-uid',
            studentNumber: null,
            fullName: 'Coach',
            email: 'coach@example.com',
            role: 'COACH',
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $court = new CourtDTO(
            id: '30000000000000000000000000000002',
            name: 'Court 2',
            description: null,
            status: 'ACTIVE',
            createdAt: '2026-09-01T08:00:00Z',
            updatedAt: '2026-09-01T08:00:00Z',
        );

        $slotA = new TimeSlotDTO(
            id: '40000000000000000000000000000002',
            startTime: '08:00:00',
            endTime: '09:00:00',
            displayOrder: 1,
            isActive: true,
        );

        $slotB = new TimeSlotDTO(
            id: '40000000000000000000000000000003',
            startTime: '09:00:00',
            endTime: '10:00:00',
            displayOrder: 2,
            isActive: true,
        );

        $this->mock(UserRepository::class, function ($mock) use ($coach) {
            $mock->shouldReceive('findByFirebaseUid')
                ->once()
                ->with('test-coach-uid')
                ->andReturn($coach);
        });

        $this->mock(AvailabilityRepository::class);

        $this->mock(TimeSlotRepository::class, function ($mock) use ($slotA, $slotB) {
            $mock->shouldReceive('listActive')
                ->once()
                ->andReturn([$slotA, $slotB]);
        });

        $this->mock(CourtRepository::class, function ($mock) use ($court) {
            $mock->shouldReceive('findById')
                ->twice()
                ->andReturn($court);
        });

        $this->mock(BookingRepository::class, function ($mock) use ($court, $slotA, $slotB) {
            $mock->shouldReceive('isSlotTaken')
                ->once()
                ->with($court->id, '2026-09-02', $slotA->id)
                ->andReturn(false);

            $mock->shouldReceive('isSlotTaken')
                ->once()
                ->with($court->id, '2026-09-02', $slotB->id)
                ->andReturn(false);

            $mock->shouldReceive('createMany')
                ->once()
                ->andReturn([
                    ['ok' => true, 'data' => ['booking_insert' => ['id' => 'booking-a']]],
                    ['ok' => true, 'data' => ['booking_insert' => ['id' => 'booking-b']]],
                ]);
        });

        $res = $this->postJson('/api/coach/team-practices', [
            'selections' => [
                [
                    'court_id' => $court->id,
                    'time_slot_id' => $slotA->id,
                    'booking_date' => '2026-09-02',
                ],
                [
                    'court_id' => $court->id,
                    'time_slot_id' => $slotB->id,
                    'booking_date' => '2026-09-02',
                ],
            ],
        ]);

        $res->assertStatus(201);
        $res->assertJsonPath('data.booking_ids.0', 'booking-a');
        $res->assertJsonPath('data.booking_ids.1', 'booking-b');
    }
}
