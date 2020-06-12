<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use App\CareAmbassadorLog;
use App\TrixField;
use Carbon\CarbonTimeZone;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Log;

class Enrollable extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
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
            $ca = auth()->user();

            throw new \Exception("Something went wrong with call queue for Care Ambassador with id: {$ca->id} Enrollable resource does not contain enrollee.");
        }

        $careAmbassador = $this->careAmbassador->careAmbassador;

        $timezone = $this->getTimezone($enrollable);

        $script = $this->getScript($enrollable);

        $familyAttributes = $this->getFamilyAttributes($enrollable);

        $phoneAttributes = $this->getPhoneAttributes($enrollable);

        $reasonAttributes = $this->getReasonAttributes($enrollable);

        $agentAttributes = $this->getAgentAttributes($enrollable);

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

        return array_merge(
            [
                'enrollable_id'            => $enrollable->id,
                'enrollable_user_id'       => optional($enrollable->user)->id,
                'practice'                 => $enrollable->practice->attributesToArray(),
                'last_call_outcome'        => $enrollable->last_call_outcome ?? '',
                'last_call_outcome_reason' => $enrollable->last_call_outcome_reason ?? '',
                'callback_note'            => $enrollable->callback_note ?? '',
                'name'                     => $enrollable->first_name.' '.$enrollable->last_name,
                'lang'                     => $enrollable->lang,
                'practice_id'              => $enrollable->practice->id,
                'practice_name'            => $enrollable->practice->display_name,
                'practice_phone'           => $enrollable->practice->outgoing_phone_number,
                'other_phone'              => $enrollable->other_phone,
                'cell_phone'               => $enrollable->cell_phone,
                'home_phone'               => $enrollable->home_phone,
                'requested_callback'       => optional($enrollable->requested_callback)->toDateString() ?? '',

                'extra' => $extra,

                'timezone' => $timezone,

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
                'script' => $script,

                'days'  => $enrollable->preferred_days ? explode(', ', $enrollable->preferred_days) : [],
                'times' => $enrollable->preferred_window
                    ? $this->timeRangeToPanelWindows($enrollable->preferred_window)
                    : [],

                'provider'       => $this->provider->attributesToArray(),
                'provider_phone' => (new StringManipulation())->formatPhoneNumber($this->provider->getPhone()),
                'has_tips'       => (bool) $this->practice->enrollmentTips,

                'is_confirmed_family' => Enrollee::statusIsToConfirm($enrollable->status),
            ],
            $familyAttributes,
            $phoneAttributes,
            $agentAttributes,
            $reasonAttributes
        );
    }

    private function getAgentAttributes($enrollable)
    {
        $preferredPhone = $enrollable->getPreferredPhoneType();

        //get agent details
        $agentPhone        = '';
        $agentEmail        = '';
        $agentRelationship = '';
        $agentName         = '';
        if ('agent' === $preferredPhone) {
            $agentPhone        = (new StringManipulation())->formatPhoneNumber($enrollable->getAgentAttribute(Enrollee::AGENT_PHONE_KEY));
            $agentName         = $enrollable->getAgentAttribute(Enrollee::AGENT_NAME_KEY);
            $agentRelationship = $enrollable->getAgentAttribute(Enrollee::AGENT_RELATIONSHIP_KEY);
            $agentEmail        = $enrollable->getAgentAttribute(Enrollee::AGENT_EMAIL_KEY);
        }

        return [
            'preferred_phone'    => $preferredPhone,
            'agent_phone'        => $agentPhone,
            'agent_email'        => $agentEmail,
            'agent_relationship' => $agentRelationship,
            'agent_name'         => $agentName,
        ];
    }

    private function getFamilyAttributes($enrollable)
    {
        //family information if exists
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

        return $familyAttributes;
    }

    private function getPhoneAttributes($enrollable)
    {
        //These phone numbers will be used to call by Twilio.
        //This will allow us to use custom numbers on non-prod environments or on production with demo practices
        $otherPhoneSanitized = isProductionEnv() && ! $enrollable->practice->is_demo
            ? $enrollable->other_phone_e164
            : $enrollable->getOriginal('other_phone');

        $cellPhoneSanitized = isProductionEnv() && ! $enrollable->practice->is_demo
            ? $enrollable->cell_phone_e164
            : $enrollable->getOriginal('cell_phone');

        $homePhoneSanitized = isProductionEnv() && ! $enrollable->practice->is_demo
            ? $enrollable->home_phone_e164
            : $enrollable->getOriginal('home_phone');

        return [
            'home_phone_sanitized'  => $homePhoneSanitized,
            'cell_phone_sanitized'  => $cellPhoneSanitized,
            'other_phone_sanitized' => $otherPhoneSanitized,
        ];
    }

    private function getReasonAttributes($enrollable)
    {
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

        return [
            'utc_reason'       => $utcReason,
            'utc_reason_other' => $utcReasonOther,
            'reason'           => $reason,
            'reason_other'     => $reasonOther,
        ];
    }

    private function getScript($enrollable): TrixField
    {
        $enrollableIsUnreachableUser = Enrollee::UNREACHABLE_PATIENT === $enrollable->source;

        if (empty($enrollable->lang)) {
            //default to english, just so we can avoid cases where something went wrong with enrollee->language
            $script = TrixField::careAmbassador(TrixField::ENGLISH_LANGUAGE, $enrollableIsUnreachableUser)->first();
        } else {
            $script = TrixField::careAmbassador($enrollable->lang, $enrollableIsUnreachableUser)->first();
        }

        if ( ! $script) {
            //default to english, just so we can avoid cases where something went wrong with enrollee->language
            $script = TrixField::careAmbassador(TrixField::ENGLISH_LANGUAGE, $enrollableIsUnreachableUser)->first();
        }

        return $script;
    }

    private function getTimezone($enrollable)
    {
        $timezone = optional($enrollable->user)->timezone;

        //check enrollee location id
        if ( ! $timezone && ! empty($enrollable->location_id)) {
            $timezone = optional($enrollable->location)->timezone;
        }

        //check ccda
        if ( ! $timezone && $enrollable->ccda) {
            $timezone = optional($enrollable->ccda->location)->timezone;
        }

        //check provider location
        $provider = $enrollable->provider;
        if ( ! $timezone && $provider) {
            $timezone = $provider->timezone;
            if ( ! $timezone) {
                //pre-loaded only locations with timezone
                $location = $provider->locations->first();
                $timezone = $location->timezone;
            }

            if ( ! $timezone) {
                //pre-loaded only locations with timezone
                $providerPractice = $provider->primaryPractice;
                $location         = $providerPractice->locations->first();
                if ($location) {
                    $timezone = $location->timezone;
                }
            }
        }

        //check practice first location that has timezone
        if ( ! $timezone) {
            $practice = $enrollable->practice;
            //pre-filter from relationship to only load locations with timezone
            $location = $practice->locations->first();
            if ($location) {
                $timezone = $location->timezone;
            }
        }

        if ($timezone) {
            try {
                $tz     = CarbonTimeZone::create($timezone);
                $region = $tz->toRegionName();
                $offset = $tz->toOffsetName();

                return  "$region ($offset)";
            } catch (\Exception $exception) {
                Log::critical("Invalid timezone for enrollee: {$enrollable->id}");

                return 'N/A';
            }
        }

        return 'N/A';
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
