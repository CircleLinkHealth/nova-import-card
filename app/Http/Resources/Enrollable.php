<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use App\CareAmbassadorLog;
use App\TrixField;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Resources\Json\Resource;

class Enrollable extends Resource
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
        /**
         * @var Enrollee
         */
        $enrollable = $this->resource;

        if ( ! $enrollable) {
            return [];
        }

        $careAmbassador = $this->careAmbassador->careAmbassador;

        $family = $enrollable->confirmedFamilyMembers;

        $familyAttributes = [];
        if ($family->isNotEmpty()) {
            $familyAttributes['family_member_names'] = $family->unique()->map(function (Enrollee $e) {
                return $e->first_name.' '.$e->last_name;
            })->filter()->implode(', ');

            $createdAt = $family->first()->pivot->created_at;
            if ($createdAt) {
                $familyAttributes['family_confirmed_at'] = $createdAt->toDateString();
            }
        }

        //These phone numbers will be used to call by Twilio.
        //This will allow us to use custom numbers on non-prod environments
        $otherPhoneSanitized = isProductionEnv()
            ? $enrollable->other_phone_e164
            : $enrollable->getOriginal('other_phone');

        $cellPhoneSanitized = isProductionEnv()
            ? $enrollable->cell_phone_e164
            : $enrollable->getOriginal('cell_phone');

        $homePhoneSanitized = isProductionEnv()
            ? $enrollable->home_phone_e164
            : $enrollable->getOriginal('home_phone');

        //we need to prefill these per CPM-2256 for confirmed family members
        $utcReason                     = '';
        $reason                        = '';
        $enrollableIsToConfirmRejected = in_array($enrollable->status, [
            Enrollee::TO_CONFIRM_REJECTED,
            Enrollee::TO_CONFIRM_SOFT_REJECTED,
        ]);

        if ( ! empty($enrollable->last_call_outcome)) {
            if (Enrollee::TO_CONFIRM_UNREACHABLE === $enrollable->status) {
                $utcReason = $enrollable->last_call_outcome;
            }
            if ($enrollableIsToConfirmRejected) {
                $reason = $enrollable->last_call_outcome;
            }
        }

        $utcReasonOther = '';
        $reasonOther    = '';
        if ( ! empty($enrollable->last_call_outcome_reason)) {
            if (Enrollee::TO_CONFIRM_UNREACHABLE === $enrollable->status) {
                $utcReasonOther = $enrollable->last_call_outcome_reason;
            }
            if ($enrollableIsToConfirmRejected) {
                $reasonOther = $enrollable->last_call_outcome_reason;
            }
        }

        //extra is the field for note on consented modal
        //we need this in case Enrollable is TO_CONFIRM_CONSENTED
        //(pre-filling consented options from previous family member)
        $extra = '';

        if (Enrollee::TO_CONFIRM_CONSENTED === $enrollable->status && ! empty($enrollable->other_note)) {
            $extra = $enrollable->other_note;
        }

        //extra field on UTC modal
        $utcNote = '';
        if (in_array($enrollable->status, [Enrollee::TO_CONFIRM_UNREACHABLE, Enrollee::UNREACHABLE])
            && ! empty($enrollable->other_note)) {
            $utcNote = $enrollable->other_note;
        }

        $preferredPhone = $this->getPreferredPhone($enrollable);

        return array_merge([
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
            'preferred_phone'          => $preferredPhone,

            'other_phone_sanitized' => $otherPhoneSanitized,
            'cell_phone_sanitized'  => $cellPhoneSanitized,
            'home_phone_sanitized'  => $homePhoneSanitized,

            'utc_reason'       => $utcReason,
            'reason'           => $reason,
            'utc_reason_other' => $utcReasonOther,
            'reason_other'     => $reasonOther,

            'extra' => $extra,

            'utc_note'        => $utcNote,
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
            'times' => $enrollable->preferred_window
                ? $this->timeRangeToPanelWindows($enrollable->preferred_window)
                : [],

            'provider'       => $this->provider->toArray(),
            'provider_phone' => (new StringManipulation())->formatPhoneNumber($this->provider->getPhone()),
            'has_tips'       => (bool) $this->practice->enrollmentTips,

            'is_confirmed_family' => Enrollee::statusIsToConfirm($enrollable->status),
        ], $familyAttributes);
    }

    private function getPreferredPhone(Enrollee $enrollable)
    {
        if (empty(trim($enrollable->primary_phone))) {
            return '';
        }

        $phones = [
            $enrollable->home_phone  => 'home',
            $enrollable->cell_phone  => 'cell',
            $enrollable->other_phone => 'other',
        ];

        $preferredPhone = isset($phones[$enrollable->primary_phone]) ? $phones[$enrollable->primary_phone] : null;

        //edge case - add primary as other phone?
        if ( ! $preferredPhone) {
            return '';
        }

        return $preferredPhone;
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
