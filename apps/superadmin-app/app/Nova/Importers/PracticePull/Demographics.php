<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Maatwebsite\Excel\Events\AfterImport;

class Demographics extends AbstractImporter
{
    public function model(array $row)
    {
        return new \CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics([
            'practice_id'              => $this->practiceId,
            'mrn'                      => $this->nullOrValue($row['patientid']),
            'first_name'               => $this->nullOrValue($row['first_name']),
            'last_name'                => $this->nullOrValue($row['last_name']),
            'last_encounter'           => Carbon::parse($row['last_encounter']),
            'dob'                      => ImportPatientInfo::parseDOBDate($this->nullOrValue($row['dob'])),
            'gender'                   => $this->nullOrValue($row['gender']),
            'lang'                     => $this->nullOrValue($row['lang']),
            'referring_provider_name'  => $this->nullOrValue($row['referring_provider_name']),
            'billing_provider_user_id' => optional(CcdaImporterWrapper::mysqlMatchProvider($row['referring_provider_name'], $this->practiceId))->id,
            'cell_phone'               => $this->nullOrValue($row['cell_phone']),
            'home_phone'               => $this->nullOrValue($row['home_phone']),
            'other_phone'              => $this->nullOrValue($row['other_phone']),
            'primary_phone'            => $this->nullOrValue($row['primary_phone']),
            'email'                    => $this->nullOrValue($row['email']),
            'street'                   => $this->nullOrValue($row['street']),
            'street2'                  => $this->nullOrValue($row['street2']),
            'city'                     => $this->nullOrValue($row['city']),
            'state'                    => $this->nullOrValue($row['state']),
            'zip'                      => $this->nullOrValue($row['zip']),
            'primary_insurance'        => $this->nullOrValue($row['primary_insurance']),
            'secondary_insurance'      => $this->nullOrValue($row['secondary_insurance']),
            'tertiary_insurance'       => $this->nullOrValue($row['tertiary_insurance']),
        ]);
    }

    /**
     * If the value of a cell is any of these we shall consider it null.
     *
     * @return array
     */
    public function nullValues()
    {
        return [
            'NA: In CPM',
            'N/A',
            '########',
            '#N/A',
            '-',
        ];
    }

    public function registerEvents(): array
    {
        return array_merge([
                           
                           ],
                           parent::registerEvents());
    }

    public function rules(): array
    {
        return $this->rules;
    }
    
    public function clearDuplicates()
    {
        return \DB::statement("
                    DELETE n1
                    FROM practice_pull_demographics n1, practice_pull_demographics n2
                    WHERE n1.id < n2.id
                    AND n1.mrn = n2.mrn
                    AND n1.practice_id = n2.practice_id
                    AND n1.practice_id = {$this->practiceId}
                    AND n2.practice_id = {$this->practiceId}
                ");
    }
}
