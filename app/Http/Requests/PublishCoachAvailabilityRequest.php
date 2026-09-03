<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PublishCoachAvailabilityRequest extends FormRequest
{
    /**
     *
     * The Coach route will separately verify the Firebase token
     * and check USERS.role = COACH on every request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            // the date for which the Coach is available
            'availability_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],

            // the Coach must select one or more fixed time slots.
            'time_slot_ids' => [
                'required',
                'array',
                'min:1',
            ],

            // validate every ID contained in the time_slot_ids array
            'time_slot_ids.*' => [
                'required',
                'uuid',
                'distinct',

            ],

        ];
    }
}
