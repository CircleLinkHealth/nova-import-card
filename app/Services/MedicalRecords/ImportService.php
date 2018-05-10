<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/10/18
 * Time: 10:11 PM
 */

namespace App\Services\MedicalRecords;

use App\Models\MedicalRecords\Ccda;
use App\Models\MedicalRecords\TabularMedicalRecord;
use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use App\Practice;
use App\User;
use Carbon\Carbon;


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
                    ->with('patient.patientInfo')
                    ->find($ccdaId);

        if ( ! $ccda) {
            $response->success = false;
            $response->message = "We could not locate CCDA with id $ccdaId";
            $response->imr     = null;

            return $response;
        }

        if ($ccda->imported) {
            if ($ccda->patient) {

            }
            $response->success = false;
            $response->message = "CCDA with id $ccdaId has already been imported.";
            $response->imr     = null;

            return $response;
        }

        if ($ccda->mrn && $ccda->practice_id) {
            $exists = User::whereHas('patientInfo', function ($q) use ($ccda) {
                $q->where('mrn_number', $ccda->mrn);
            })->whereProgramId($ccda->practice_id)
                          ->first();

            if ($exists) {
                $response->success = false;
                $response->message = "CCDA with id $ccdaId has already been imported.";
                $response->imr     = null;

                return $response;
            }
        }

        $imr = $ccda->import();

        $update = Ccda::whereId($ccdaId)
                      ->update([
                          'status'   => Ccda::QA,
                          'imported' => true,
                      ]);

        $response->success = true;
        $response->message = "CCDA successfully imported.";
        $response->imr     = $imr;

        return $response;
    }

    public function isCcda($medicalRecordType)
    {
        return stripcslashes($medicalRecordType) == stripcslashes(Ccda::class);
    }

    /**
     * Create a TabularMedicalRecord for each row, and import it.
     *
     * @param $row
     *
     * @param Practice $practice
     *
     * @return \App\Models\MedicalRecords\ImportedMedicalRecord
     * @throws \Exception
     */
    public function createTabularMedicalRecordAndImport($row, Practice $practice)
    {
        $row['dob']         = $row['dob']
            ? Carbon::parse($row['dob'])->toDateString()
            : null;
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

        $exists = TabularMedicalRecord::where([
            'first_name' => $row['first_name'],
            'last_name'  => $row['last_name'],
            'dob'        => $row['dob'],
        ])->first();

        if ($exists) {
            if ($exists->importedMedicalRecord()) {
                return null;
            }

            $exists->delete();
        }

        if ($this->practice->id == 139) {
            $mrn = $this->lookupPHXmrn($row['first_name'], $row['last_name'], $row['dob']);

            if ( ! $mrn) {
                throw new \Exception("Phoenix Heart Patient not found");
            }

            $row['mrn'] = $mrn;
        }

        $mr = TabularMedicalRecord::create($row);

        return $mr->import();
    }

    private function lookupPHXmrn($firstName, $lastName, $dob)
    {
        $dob = Carbon::parse($dob)->format('n/j/Y');

        $row = PhoenixHeartName::where('patient_first_name', $firstName)
                               ->where('patient_last_name', $lastName)
                               ->where('dob', $dob)
                               ->first();

        if ($row && $row->patient_id) {
            return $row->patient_id;
        }

        return null;
    }
}