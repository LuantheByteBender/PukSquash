<?php

namespace Tests\Feature;

use App\Http\Requests\PublishCoachAvailabilityRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PublishCoachAvailabilityRequestTest extends TestCase
{
    // a correctly formatted example UUID representing a time slot
    private const SLOT_ID = '00000000-0000-4000-8000-000000000001';

    /**
     * correct availability information must pass validation
     */
    public function test_valid_availability_passes_validation(): void
    {
        // create the information that a future Coach frontend would submit
        $data = [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [self::SLOT_ID],
        ];

        // Apply the rules from PublishCoachAvailabilityRequest to the data
        $validator = Validator::make(
            $data,
            (new PublishCoachAvailabilityRequest())->rules()
        );

        // the validator should find no errors
        $this->assertFalse($validator->fails());
    }

    /**
     * both required fields must be provided
     */
    public function test_required_fields_cannot_be_missing(): void
    {
        $validator = Validator::make(
            [],
            (new PublishCoachAvailabilityRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('availability_date'));
        $this->assertTrue($validator->errors()->has('time_slot_ids'));
    }

    /**
     * past dates and incorrectly formatted slot IDs must be rejected
     */
    public function test_invalid_date_and_time_slot_id_are_rejected(): void
    {
        $data = [
            'availability_date' => now()->subDay()->format('Y-m-d'),
            'time_slot_ids' => ['not-a-uuid'],
        ];

        $validator = Validator::make(
            $data,
            (new PublishCoachAvailabilityRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('availability_date'));
        $this->assertTrue($validator->errors()->has('time_slot_ids.0'));
    }

    /**
     * the same time slot must not be submitted twice
     */
    public function test_duplicate_time_slots_are_rejected(): void
    {
        $data = [
            'availability_date' => now()->addDay()->format('Y-m-d'),
            'time_slot_ids' => [
                self::SLOT_ID,
                self::SLOT_ID,
            ],
        ];

        $validator = Validator::make(
            $data,
            (new PublishCoachAvailabilityRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('time_slot_ids.0'));
    }
}