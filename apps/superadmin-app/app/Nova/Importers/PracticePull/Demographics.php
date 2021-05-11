<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use CircleLinkHealth\Core\UpdateOrCreateInDb;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\SharedModels\Entities\PracticePull\Demographics as DemographicsModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class Demographics implements WithChunkReading, WithHeadingRow, ShouldQueue, OnEachRow
{
    use Importable;

    /**
     * @var int
     */
    private $practiceId;

    /**
     * Medications constructor.
     */
    public function __construct(int $practiceId)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $this->practiceId = $practiceId;
    }

    public function batchSize(): int
    {
        return 300;
    }

    public function chunkSize(): int
    {
        return 300;
    }

    /**
     * Returns null if value means N/A or equivalent. Otherwise returns the value passed to it.
     *
     * @param string $value
     *
     * @return string|null
     */
    public function nullOrValue($value)
    {
        return empty($value) || in_array($value, $this->nullValues())
            ? null
            : $value;
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

    public function onRow(Row $row)
    {
        UpdateOrCreateInDb::dispatch(
            DemographicsModel::class,
            [
                'practice_id' => $this->practiceId,
                'mrn'         => $this->nullOrValue($row['patientid']),
                'first_name'  => $this->nullOrValue($row['first_name']),
                'last_name'   => $this->nullOrValue($row['last_name']),
                'dob'         => ImportPatientInfo::parseDOBDate($this->nullOrValue($row['dob'])),
            ],
            [
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
                'last_encounter'           => Carbon::parse($row['last_encounter']),
                'gender'                   => $this->nullOrValue($row['gender']),
            ]
        );
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
