<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\CcdaImporter\Tasks;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\CcdaImporter\BaseCcdaImportTask;
use CircleLinkHealth\Eligibility\CcdaImporter\Traits\FiresImportingHooks;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use  Illuminate\Support\Str;

class ImportPatientInfo extends BaseCcdaImportTask
{
    use FiresImportingHooks;
    const HOOK_IMPORTED_PATIENT_INFO = 'IMPORTED_PATIENTINFO';

    const HOOK_IMPORTING_PATIENT_INFO = 'IMPORTING_PATIENTINFO';

    /**
     * @var Enrollee
     */
    private $enrollee;

    public static function for(User $patient, Ccda $ccda, Enrollee $enrollee = null)
    {
        $static = new static($patient, $ccda);
        if ($enrollee instanceof Enrollee) {
            $static->setEnrollee($enrollee);
        }

        return $static->import();
    }

    /**
     * @param $dob
     *
     * @throws \Exception
     */
    public static function parseDOBDate($dob): ?Carbon
    {
        if ($dob instanceof Carbon) {
            return self::correctCenturyIfNeeded($dob);
        }

        if (empty($dob)) {
            return null;
        }

        try {
            $date = Carbon::parse($dob);

            if ($date->isToday()) {
                throw new \InvalidArgumentException('date note parsed correctly');
            }

            return self::correctCenturyIfNeeded($date);
        } catch (\InvalidArgumentException $e) {
            if (Str::contains($dob, '/')) {
                $delimiter = '/';
            } elseif (Str::contains($dob, '-')) {
                $delimiter = '-';
            }
            $date = explode($delimiter, $dob);

            if (count($date) < 3) {
                throw new \Exception("Invalid date $dob");
            }

            $year = $date[2];

            if (2 == strlen($year)) {
                //if date is two digits we are assuming it's from the 1900s
                $year = (int) $year + 1900;
            }

            return Carbon::createFromDate($year, $date[0], $date[1]);
        }
    }

    /**
     * @param mixed $enrollee
     */
    public function setEnrollee(Enrollee $enrollee): void
    {
        $this->enrollee = $enrollee;
    }

    protected function import()
    {
        $this->patient->load('patientInfo');

        $demographics = $this->transform($this->ccda->bluebuttonJson()->demographics);

        $mrn = $demographics['mrn_number'];

        $agentDetails = $this->getEnrolleeAgentDetailsIfExist();

        $args = array_merge(
            [
                'ccda_id'      => $this->ccda->id,
                'birth_date'   => self::parseDOBDate($demographics['dob']),
                'consent_date' => now()->toDateString(),
                'gender'       => call_user_func(function () use (
                    $demographics
                ) {
                    $maleVariations = [
                        'm',
                        'male',
                        'man',
                    ];

                    $femaleVariations = [
                        'f',
                        'female',
                        'woman',
                    ];

                    if (in_array(strtolower($demographics['gender']), $maleVariations)) {
                        $gender = 'M';
                    } else {
                        if (in_array(strtolower($demographics['gender']), $femaleVariations)) {
                            $gender = 'F';
                        }
                    }

                    return empty($gender)
                        ?: $gender;
                }),
                'mrn_number'                 => $mrn,
                'preferred_contact_language' => call_user_func(
                    function () use (
                        $demographics
                    ) {
                        $englishVariations = [
                            'english',
                            'eng',
                            'en',
                            'e',
                        ];

                        $spanishVariations = [
                            'spanish',
                            'es',
                        ];

                        $default = 'EN';

                        if (in_array(strtolower($demographics['language']), $englishVariations)) {
                            $language = 'EN';
                        } else {
                            if (in_array(strtolower($demographics['language']), $spanishVariations)) {
                                $language = 'ES';
                            }
                        }

                        return empty($language)
                            ? $default
                            : $language;
                    }
                ),
                'preferred_contact_method' => 'CCT',
                'registration_date'        => $this->patient->user_registered->toDateString(),
                'general_comment'          => $this->enrollee()
                    ? $this->enrollee()->other_note
                    : null,
            ],
            $agentDetails
        );

        $hook = $this->fireImportingHook(self::HOOK_IMPORTING_PATIENT_INFO, $this->patient, $this->ccda, $args);

        if (is_array($hook)) {
            $args = $hook;
        }

        $patientInfo = Patient::updateOrCreate(
            [
                'user_id' => $this->patient->id,
            ],
            $args
        );

        if ( ! $patientInfo->mrn_number) {
            $patientInfo->mrn_number = $args['mrn_number'];
        }

        if ( ! $patientInfo->birth_date) {
            $patientInfo->birth_date = $args['birth_date'];
        }

        if ( ! $patientInfo->ccda_id) {
            $patientInfo->ccda_id = $args['ccda_id'];
        }

        if ( ! $patientInfo->ccm_status) {
            $patientInfo->ccm_status = Patient::ENROLLED;
        }

        if ( ! $patientInfo->consent_date) {
            $patientInfo->consent_date = $args['consent_date'];
        }

        if ( ! $patientInfo->gender) {
            $patientInfo->gender = $args['gender'];
        }

        if ( ! $patientInfo->preferred_contact_language) {
            $patientInfo->preferred_contact_language = $args['preferred_contact_language'];
        }

        if ( ! $patientInfo->preferred_contact_location && $this->ccda->location_id) {
            $patientInfo->preferred_contact_location = $this->ccda->location_id;
        }

        if ( ! $patientInfo->preferred_contact_method) {
            $patientInfo->preferred_contact_method = $args['preferred_contact_method'];
        }

        if ( ! $patientInfo->agent_name) {
            $patientInfo->agent_name = $args['agent_name'] ?? null;
        }

        if ( ! $patientInfo->agent_telephone) {
            $patientInfo->agent_telephone = $args['agent_telephone'] ?? null;
        }

        if ( ! $patientInfo->agent_email) {
            $patientInfo->agent_email = $args['agent_email'] ?? null;
        }

        if ( ! $patientInfo->agent_relationship) {
            $patientInfo->agent_relationship = $args['agent_relationship'] ?? null;
        }

        if ( ! $patientInfo->registration_date) {
            $patientInfo->registration_date = $args['registration_date'];
        }

        if ( ! $patientInfo->general_comment) {
            $patientInfo->general_comment = $args['general_comment'] ?? null;
        }

        if ($patientInfo->isDirty()) {
            $patientInfo->save();
        }

        $this->fireImportingHook(self::HOOK_IMPORTED_PATIENT_INFO, $this->patient, $this->ccda, $patientInfo);
    }

    /**
     * Subtracts 100 years off date if it's after 1/1/2000.
     *
     * @return Carbon
     */
    private static function correctCenturyIfNeeded(Carbon &$date)
    {
        //If a DOB is after 2000 it's because at some point the date incorrectly assumed to be in the 2000's, when it was actually in the 1900's. For example, this date 10/05/04.
        $cutoffDate = Carbon::createFromDate(2000, 1, 1);

        if ($date->gte($cutoffDate)) {
            $date->subYears(100);
        }

        return $date;
    }

    private function enrollee(): ?Enrollee
    {
        if ( ! $this->enrollee) {
            $this->enrollee = Enrollee::where(
                [
                    ['user_id', '=', $this->patient->id],
                    ['practice_id', '=', $this->patient->program_id],
                    ['first_name', '=', $this->patient->first_name],
                    ['last_name', '=', $this->patient->last_name],
                ]
            )->first();
        }

        return $this->enrollee;
    }

    /**
     * If Enrollee exists and if agent details are set,
     * Get array to save in patient info.
     *
     * @return array
     */
    private function getEnrolleeAgentDetailsIfExist()
    {
        if ( ! $this->enrollee()) {
            return [];
        }
        if (empty($this->enrollee()->agent_details)) {
            return [];
        }

        return [
            'agent_name'         => $this->enrollee()->getAgentAttribute(Enrollee::AGENT_NAME_KEY),
            'agent_telephone'    => $this->enrollee()->getAgentAttribute(Enrollee::AGENT_PHONE_KEY),
            'agent_email'        => $this->enrollee()->getAgentAttribute(Enrollee::AGENT_EMAIL_KEY),
            'agent_relationship' => $this->enrollee()->getAgentAttribute(Enrollee::AGENT_RELATIONSHIP_KEY),
        ];
    }

    private function transform(object $demographics): array
    {
        return $this->getTransformer()->demographics($demographics);
    }
}
