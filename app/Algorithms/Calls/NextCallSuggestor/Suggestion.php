<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls\NextCallSuggestor;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Support\Arrayable;

class Suggestion implements Arrayable
{
    public string $attempt_note;
    public bool $ccm_time_achieved;
    public int $ccmTimeInSeconds;
    public string $date;
    public string $formatted_monthly_time;
    public string $logic;
    public Carbon $nextCallDate;
    public int $no_of_successful_calls;
    public ?int $nurse;
    public ?string $nurse_display_name;
    public User $patient;
    public string $predicament;
    public bool $successful;
    public string $window_end;
    public string $window_match;
    public string $window_start;

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'attempt_note'           => $this->attempt_note ?? null,
            'ccm_time_achieved'      => $this->ccm_time_achieved ?? null,
            'date'                   => $this->date ?? null,
            'formatted_monthly_time' => $this->formatted_monthly_time ?? null,
            'logic'                  => $this->logic ?? null,
            'nextCallDate'           => $this->nextCallDate ?? null,
            'no_of_successful_calls' => $this->no_of_successful_calls ?? null,
            'nurse'                  => $this->nurse ?? null,
            'nurse_display_name'     => $this->nurse_display_name ?? null,
            'patient'                => $this->patient ?? null,
            'predicament'            => $this->predicament ?? null,
            'successful'             => $this->successful ?? null,
            'window_end'             => $this->window_end ?? null,
            'window_match'           => $this->window_match ?? null,
            'window_start'           => $this->window_start ?? null,
            'ccmTimeInSeconds'       => $this->ccmTimeInSeconds ?? null,
        ];
    }
}
