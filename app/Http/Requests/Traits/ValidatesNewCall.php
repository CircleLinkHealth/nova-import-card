<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Requests\Traits;

use App\Rules\DateBeforeUsingCarbon;

trait ValidatesNewCall
{
    public function newCallValidationRules()
    {
        return [
            'type'            => 'required',
            'sub_type'        => 'required_if:type,task',
            'outbound_cpm_id' => '',
            'scheduled_date'  => ['required', 'after_or_equal:today', new DateBeforeUsingCarbon()],
            'window_start'    => 'required|date_format:H:i',
            'window_end'      => 'required|date_format:H:i',
            'attempt_note'    => '',
            'is_manual'       => 'required|boolean',
            'family_override' => '',
            'asap'            => '',
            'is_reschedule'   => 'sometimes|boolean',
        ];
    }
}
