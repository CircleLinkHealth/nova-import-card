<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\MedicalRecordImporter;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;

class ImportService
{
    /**
     * Import a Patient whose CCDA we have already.
     *
     * @param $ccdaId
     *
     * @return \stdClass
     */
    public function importExistingCcda($ccdaId)
    {
        $response = new \stdClass();

        $ccda = Ccda::withTrashed()
            ->with(['patient.patientInfo', 'media'])
            ->find($ccdaId);

        if ( ! $ccda) {
            $response->success = false;
            $response->message = "We could not locate CCDA with id ${ccdaId}";
            $response->imr     = null;

            return $response;
        }

        if ($ccda->imported) {
            if ($ccda->patient) {
                $response->success = false;
                $response->message = "CCDA with id ${ccdaId} has already been imported.";
                $response->imr     = null;

                return $response;
            }
        }

        if ($ccda->mrn && $ccda->practice_id) {
            $exists = User::whereHas(
                'patientInfo',
                function ($q) use ($ccda) {
                    $q->where('mrn_number', $ccda->mrn);
                }
            )->whereProgramId($ccda->practice_id)
                ->exists();

            if ($exists) {
                $response->success = false;
                $response->message = "CCDA with id ${ccdaId} has already been imported.";
                $response->imr     = null;

                return $response;
            }
        }

        $imr = $ccda->import();

        $ccda->status   = Ccda::QA;
        $ccda->imported = true;
        $ccda->save();

        $response->success = true;
        $response->message = 'CCDA successfully imported.';
        $response->imr     = $imr;

        return $response;
    }

    public function isCcda($medicalRecordType)
    {
        return stripcslashes($medicalRecordType) == stripcslashes(Ccda::class);
    }

    /**
     * Subtracts 100 years off date if it's after 1/1/2000.
     *
     * @return Carbon
     */
    private function correctCenturyIfNeeded(Carbon &$date)
    {
        //If a DOB is after 2000 it's because at some point the date incorrectly assumed to be in the 2000's, when it was actually in the 1900's. For example, this date 10/05/04.
        $cutoffDate = Carbon::createFromDate(2000, 1, 1);

        if ($date->gte($cutoffDate)) {
            $date->subYears(100);
        }

        return $date;
    }

    /**
     * @param $dob
     *
     * @throws \Exception
     *
     * @return Carbon|null
     */
    private function parseDOBDate($dob)
    {
        if ($dob instanceof Carbon) {
            return $this->correctCenturyIfNeeded($dob);
        }

        try {
            $date = Carbon::parse($dob);

            if ($date->isToday()) {
                throw new \InvalidArgumentException('date note parsed correctly');
            }

            return $this->correctCenturyIfNeeded($date);
        } catch (\InvalidArgumentException $e) {
            if ( ! $dob) {
                return null;
            }

            if (str_contains($dob, '/')) {
                $delimiter = '/';
            } elseif (str_contains($dob, '-')) {
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
}
