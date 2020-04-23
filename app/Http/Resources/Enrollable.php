<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use App\CareAmbassadorLog;
use App\TrixField;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Resources\Json\JsonResource;

class Enrollable extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        //enrollable is enrollee at this point
        $enrollable = $this->resource;

        if ( ! $enrollable) {
            return [];
        }

        $careAmbassador = $this->careAmbassador->careAmbassador;

        $enrollable->load(['practice']);

        return [
            'enrollable_id'            => $enrollable->id,
            'enrollable_user_id'       => optional($enrollable->user)->id,
            'practice'                 => $enrollable->practice->toArray(),
            'last_call_outcome'        => $enrollable->last_call_outcome ?? '',
            'last_call_outcome_reason' => $enrollable->last_call_outcome_reason ?? '',
            'name'                     => $enrollable->first_name.' '.$enrollable->last_name,
            'lang'                     => $enrollable->lang,
            'practice_id'              => $enrollable->practice->id,
            'practice_name'            => $enrollable->practice->display_name,
            'practice_phone'           => $enrollable->practice->outgoing_phone_number,
            'other_phone'              => $enrollable->other_phone,
            'cell_phone'               => $enrollable->cell_phone,
            'home_phone'               => $enrollable->home_phone,

            //these phone numbers will be used to call by Twilio. This will allow us to use custom numbers on non-prod environments
            'other_phone_sanitized' => isProductionEnv()
                ? $enrollable->other_phone_e164
                : $enrollable->getOriginal('other_phone'),
            'cell_phone_sanitized' => isProductionEnv()
                ? $enrollable->cell_phone_e164
                : $enrollable->getOriginal('cell_phone'),
            'home_phone_sanitized' => isProductionEnv()
                ? $enrollable->home_phone_e164
                : $enrollable->getOriginal('home_phone'),

            //we need to prefill these per CPM-2256 for confirmed family members
            'utc_reason' => Enrollee::UNREACHABLE === $enrollable->status && $enrollable->last_call_outcome
                ? $enrollable->last_call_outcome
                : '',
            'reason' => Enrollee::UNREACHABLE !== $enrollable->status && $enrollable->last_call_outcome
                ? $enrollable->last_call_outcome
                : '',
            'utc_reason_other' => Enrollee::UNREACHABLE === $enrollable->status && $enrollable->last_call_outcome_reason
                ? $enrollable->last_call_outcome_reason
                : '',
            'reason_other' => Enrollee::UNREACHABLE !== $enrollable->status && $enrollable->last_call_outcome_reason
                ? $enrollable->last_call_outcome_reason
                : '',
            'extra'           => $enrollable->other_note,
            'last_encounter'  => $enrollable->last_encounter ?? 'N/A',
            'attempt_count'   => $enrollable->attempt_count ?? 0,
            'last_attempt_at' => optional($enrollable->last_attempt_at)->toDateString() ?? 'N/A',
            'address'         => $enrollable->address,
            'address_2'       => $enrollable->address_2,
            'state'           => $enrollable->state,
            'zip'             => $enrollable->zip,
            'email'           => $enrollable->email,
            'city'            => $enrollable->city,
            'dob'             => optional($enrollable->dob)->toDateString() ?? 'N/A',

            'report' => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
            'script' => TrixField::careAmbassador($this->lang)->first(),

            'days'  => $enrollable->preferred_days ? explode(', ', $enrollable->preferred_days) : [],
            'times' => $enrollable->preferred_window ? $this->timeRangeToPanelWindows($enrollable->preferred_window) : [],

            'provider'       => $this->provider->toArray(),
            'provider_phone' => (new StringManipulation())->formatPhoneNumber($this->provider->getPhone()),
            'has_tips'       => (bool) $this->practice->enrollmentTips,
        ];
    }

    private function timeRangeToPanelWindows(string $timeRange)
    {
        $times = collect(explode('-', $timeRange));

        if (2 !== $times->count()) {
            return null;
        }

        $start = $times->first();
        $end   = $times->last();

        $panelWindows = [];

        //Boolean algebra ftw yo
        if ('09:00' == $start) {
            $panelWindows[] = '09:00-12:00';
        }

        if ('15:00' == $end || '18:00' == $end) {
            $panelWindows[] = '12:00-15:00';
        }

        if ('18:00' == $end) {
            $panelWindows[] = '15:00-18:00';
        }

        return $panelWindows;
    }
}
