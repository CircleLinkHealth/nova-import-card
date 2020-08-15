<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Calls;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

class NurseFinder
{
    protected $data;
    protected $matchArray;

    //not used anymore
    //protected $nursesForPatient;

    protected $offsetDate;

    /*
     * Locates an RN for a given patient
     * Use function handle to get (Nurse, target date, start window and end window)
     */

    /** @var Patient patient */
    protected $patient;
    protected $previousCall;
    protected $windowEnd;
    protected $windowStart;

    public function __construct(
        Patient $patient,
        Carbon $date = null,
        $windowStart = null,
        $windowEnd = null,
        Call $previousCall = null
    ) {
        $this->patient      = $patient;
        $this->offsetDate   = $date;
        $this->windowStart  = $windowStart;
        $this->windowEnd    = $windowEnd;
        $this->previousCall = $previousCall;
    }

    public function find()
    {
        $user               = auth()->user();
        $isCurrentUserNurse = optional($user)->isCareCoach() ?? false;

        $patientNurseUsers = $this->patient->getNurses();
        if ($patientNurseUsers) {
            $patientNurseUser = $patientNurseUsers['temporary'] ?? $patientNurseUsers['permanent'];

            if ($patientNurseUser) {
                $match['nurse']              = $patientNurseUser['user']->id;
                $match['nurse_display_name'] = $patientNurseUser['user']->display_name;
                $match['window_match']       = "Assigning next call to {$patientNurseUser['user']->display_name}.";

                if (isset($patientNurseUsers['temporary'])) {
                    $match['temporary_from'] = $patientNurseUser['from'];
                    $match['temporary_to']   = $patientNurseUser['to'];

                    if (isset($patientNurseUsers['permanent'])) {
                        $alt                             = $patientNurseUsers['permanent']['user'];
                        $match['nurse_alt']              = $alt->id;
                        $match['nurse_display_name_alt'] = $alt->display_name;
                        $match['window_match_alt']       = "Assigning next call to $alt->display_name.";
                    }
                }

                return $match;
            }
        }

        if ($isCurrentUserNurse) {
            $match['nurse']        = auth()->id();
            $match['window_match'] = 'Assigning next call to current care coach.';

            return $match;
        }

        $isPreviousCallNurseActive = false;
        $previousCallUser          = User::ofType('care-center')
            ->whereHas('nurseInfo', function ($q) {
                $q->where('status', 'active');
            })
            ->with('nurseInfo')
            ->find($this->previousCall['outbound_cpm_id']);

        if ($previousCallUser) {
            $isPreviousCallNurseActive = true;
        }

        $nurseDisplayName = '';

        if ($isPreviousCallNurseActive && '' == $this->previousCall['attempt_note']) {
            $match['nurse']        = $this->previousCall['outbound_cpm_id'];
            $match['window_match'] = 'Attempt Note was empty, assigning to care person that last contacted patient. ';
            $nurseDisplayName      = $previousCallUser->display_name;
        } else {
            $data = $this->getLastRNCallWithoutAttemptNote($this->patient, $this->previousCall['outbound_cpm_id']);

            if ('' != $this->previousCall['attempt_note']) {
                $match['window_match'] = 'Attempt Note present, looking for last care person that contacted patient without one..';
            } else {
                $match['window_match'] = '';
            }

            if (null != $data) {
                $match['nurse'] = $data->id;
                $match['window_match'] .= ' Found care person that contacted patient in the past without attempt note. ';
                $nurseDisplayName = $data->display_name;
            } elseif ($isPreviousCallNurseActive) {
                //assign back to RN that first called patient
                $match['nurse'] = $this->previousCall['outbound_cpm_id'];
                $match['window_match'] .= ' No previous care person without attempt note found, assigning to last contacted care person. ';
                $nurseDisplayName = $previousCallUser->display_name;
            } else {
                return null;
            }
        }

        /*
         *
        Always schedule next call to RN who made last call EXCEPT:
        if attempt note exists for a call: then assign next call to RN who performed the last call without an attempt note
        note: if there is no such RN (ex: first call ever has an attempt note), then assign next call to RN who made last call
         */

        $match['window_match'] .= '('.$nurseDisplayName.')';
        $match['nurse_display_name'] = $nurseDisplayName;

        return $match;
    }

    /**
     * Get last RN without attempt note.
     *
     * Edit (Pangratios) - also filters out nurses that are not active
     *
     * @param \CircleLinkHealth\Customer\Entities\User $patient
     * @param int                                      $nurseToIgnore
     *
     * @return \CircleLinkHealth\Customer\Entities\User|null
     */
    public function getLastRNCallWithoutAttemptNote($patient, $nurseToIgnore)
    {
        $user = optional(Call
            ::where('inbound_cpm_id', $patient->user_id)
                ->where('status', '!=', 'scheduled')
                ->where('called_date', '!=', '')
                ->where('attempt_note', '=', '')
                ->where('outbound_cpm_id', '!=', $nurseToIgnore)
                ->whereHas('outboundUser', function ($q) {
                    $q->whereHas('nurseInfo', function ($q2) {
                        $q2->where('status', '=', 'active');
                    });
                })
                ->orderBy('called_date', 'desc')
                ->first())->outboundUser;

        return $user;
    }
}
