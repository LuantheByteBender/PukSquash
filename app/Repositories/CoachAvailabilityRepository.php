<?php

namespace App\Repositories;

use App\DataTransferObjects\CoachAvailabilityDTO;
use App\Services\SqlConnectClient;

class CoachAvailabilityRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return CoachAvailabilityDTO[] */
    public function getAvailableCoaches(string $availabilityDate, string $timeSlotId): array
    {
        $data = $this->client->execute('GetAvailableCoaches', [
            'availabilityDate' => $availabilityDate,
            'timeSlotId' => $timeSlotId,
        ]);

        return array_map(
            fn (array $row) => CoachAvailabilityDTO::fromGraphQL($row),
            $data['coachAvailabilities'] ?? [],
        );
    }

    public function create(string $coachId, string $timeSlotId, string $availabilityDate): void
    {
        $this->client->execute('CreateCoachAvailability', [
            'coachId' => $coachId,
            'timeSlotId' => $timeSlotId,
            'availabilityDate' => $availabilityDate,
        ]);
    }

    public function createMany(array $rows): array
    {
        return $this->client->executeMany(
            'CreateCoachAvailability',
            array_map(fn (array $row) => [
                'coachId' => $row['coachId'],
                'timeSlotId' => $row['timeSlotId'],
                'availabilityDate' => $row['availabilityDate'],
            ], $rows),
        );
    }

    public function isCoachAvailable(string $availabilityDate, string $timeSlotId, string $coachId): bool
    {
        $data = $this->client->execute('IsCoachAvailable', [
            'availabilityDate' => $availabilityDate,
            'timeSlotId' => $timeSlotId,
            'coachId' => $coachId,
        ]);

        return ! empty($data['coachAvailabilities'] ?? []);
    }

    /**
     * get one Coach's published time slots for a selected date
     *
     * @return CoachAvailabilityDTO[]
     */
    public function getForCoachAndDate(
        string $coachId,
        string $availabilityDate,
    ): array {
        // execute the query using filter values
        $data = $this->client->execute('GetCoachAvailabilityForDate', [
            'coachId' => $coachId,
            'availabilityDate' => $availabilityDate,
        ]);

        // convert each returned row into the existing availability DTO
        return array_map(
            fn (array $row) => CoachAvailabilityDTO::fromGraphQL($row),
            $data['coachAvailabilities'] ?? [],
        );
    }

    /**
     * remove one Coach's published availability using its composite key
     */
    public function deleteForCoachAndSlot(
        string $coachId,
        string $timeSlotId,
        string $availabilityDate,
    ): void {
        // pass all three key values to the GraphQL mutation
        $this->client->execute('DeleteCoachAvailabilityForSlot', [
            'coachId' => $coachId,
            'timeSlotId' => $timeSlotId,
            'availabilityDate' => $availabilityDate,
        ]);
    }

    /**
     * check whether a confirmed booking uses this coach's selected slot
     */
    public function hasConfirmedBooking(
        string $coachId,
        string $availabilityDate,
        string $timeSlotId,
    ): bool {
        // check bookings across all courts for this coach, date and slot
        $data = $this->client->execute('GetConfirmedBookingForCoachSlot', [
            'coachId' => $coachId,
            'bookingDate' => $availabilityDate,
            'timeSlotId' => $timeSlotId,
        ]);

        // true means a booking exists, so removal must be blocked
        return ! empty($data['bookings'] ?? []);
    }

    public function listConfirmedBookingTimeSlotIdsForDate(
        string $coachId,
        string $availabilityDate,
    ): array {
        $data = $this->client->execute('GetConfirmedBookingsForCoachDate', [
            'coachId' => $coachId,
            'bookingDate' => $availabilityDate,
        ]);

        $ids = [];
        foreach (($data['bookings'] ?? []) as $row) {
            $timeSlotId = $row['timeSlotId'] ?? null;

            if (! $timeSlotId) {
                continue;
            }

            $ids[$timeSlotId] = true;
        }

        return array_keys($ids);
    }
}
