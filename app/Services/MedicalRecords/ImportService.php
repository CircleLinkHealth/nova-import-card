<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\MedicalRecords;

use App\Enrollee;
use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;

class ImportService
{
    /**
     * Create a TabularMedicalRecord for each row, and import it.
     *
     * @param $row
     *
     * @throws \Exception
     *
     * @return \App\Models\MedicalRecords\ImportedMedicalRecord
     */
    public function createTabularMedicalRecordAndImport($row, Practice $practice)
    {
        $row['dob']         = $this->parseDate($row['dob']);
        $row['practice_id'] = $practice->id;
        $row['location_id'] = $practice->primary_location_id;

        if (array_key_exists('consent_date', $row)) {
            $row['consent_date'] = Carbon::parse($row['consent_date'])->format('Y-m-d');
        }

        if (array_key_exists('street', $row)) {
            $row['address'] = $row['street'];
        }

        if (array_key_exists('street_2', $row)) {
            $row['address2'] = $row['street_2'];
        }

        if (array_key_exists('problems', $row) & ! array_key_exists('problems_string', $row)) {
            $row['problems_string'] = $row['problems'];
        }

        if (array_key_exists('referring_provider_name', $row) & ! array_key_exists('provider_name', $row)) {
            $row['provider_name'] = $row['referring_provider_name'];
        }

        if (array_key_exists('primary_phone', $row) && array_key_exists('primary_phone_type', $row)) {
            if (str_contains(strtolower($row['primary_phone_type']), ['cell', 'mobile'])) {
                $row['cell_phone'] = $row['primary_phone'];
            } elseif (str_contains(strtolower($row['primary_phone_type']), 'home')) {
                $row['home_phone'] = $row['primary_phone'];
            } elseif (str_contains(strtolower($row['primary_phone_type']), 'work')) {
                $row['work_phone'] = $row['primary_phone'];
            }
        }

        if (array_key_exists('alt_phone', $row) && array_key_exists('alt_phone_type', $row)) {
            if (str_contains(strtolower($row['alt_phone_type']), ['cell', 'mobile'])) {
                $row['cell_phone'] = $row['alt_phone'];
            } elseif (str_contains(strtolower($row['alt_phone_type']), 'home')) {
                $row['home_phone'] = $row['alt_phone'];
            } elseif (str_contains(strtolower($row['alt_phone_type']), 'work')) {
                $row['work_phone'] = $row['alt_phone'];
            }
        }

        $exists = TabularMedicalRecord::where(
            [
                'first_name' => $row['first_name'],
                'last_name'  => $row['last_name'],
                'dob'        => $row['dob'],
            ]
        )->first();

        if ($exists) {
            if ( ! $exists->importedMedicalRecord()) {
                $exists->delete();
            }
        }

        if (139 == $practice->id) {
            $mrn = $this->lookupPHXmrn($row['first_name'], $row['last_name'], $row['dob'], $row['mrn']);

            if ( ! $mrn) {
                throw new \Exception('Phoenix Heart Patient not found');
            }

            $row['mrn'] = $mrn;
        }

        $mr = TabularMedicalRecord::create($row);

        return $mr->import();
    }

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

    /**
     * @throws \Exception
     *
     * @return \App\Models\MedicalRecords\ImportedMedicalRecord
     */
    public function importPHXEnrollee(Enrollee $enrollee)
    {
        $phx     = Practice::whereName('phoenix-heart')->firstOrFail();
        $patient = $enrollee->toArray();

        $imr = $this->createTabularMedicalRecordAndImport($patient, $phx);

        if ( ! $imr) {
            return null;
        }

        $enrollee->medical_record_type = $imr->medical_record_type;
        $enrollee->medical_record_id   = $imr->medical_record_id;
        $enrollee->save();
    }

    public function isCcda($medicalRecordType)
    {
        return stripcslashes($medicalRecordType) == stripcslashes(Ccda::class);
    }

    private function lookupPHXmrn($firstName, $lastName, $dob, $mrn)
    {
        $dob = Carbon::parse($dob)->format('n/j/Y');

        $row = PhoenixHeartName::where('patient_first_name', $firstName)
            ->where('patient_last_name', $lastName)
            ->where('dob', $dob)
            ->first();

        if ($row && $row->patient_id && empty($mrn)) {
            return $row->patient_id;
        }

        $row = PhoenixHeartName::where('patient_id', $mrn)
            ->first();

        if ($row && $row->patient_id) {
            return $row->patient_id;
        }

        return null;
    }

    private function parseDate($dob)
    {
        try {
            $date = Carbon::parse($dob);

            if ($date->isToday()) {
                throw new \InvalidArgumentException('date note parsed correctly');
            }
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
