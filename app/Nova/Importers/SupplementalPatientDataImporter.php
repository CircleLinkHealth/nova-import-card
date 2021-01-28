<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\SharedModels\Entities\SupplementalPatientData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class SupplementalPatientDataImporter implements WithChunkReading, WithHeadingRow, ShouldQueue, OnEachRow
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
        return 400;
    }

    public function chunkSize(): int
    {
        return 400;
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

    public function onRow(Row $rowObj)
    {
        $row  = $rowObj->toArray();
        $args = [
            'dob' => optional(
                ImportPatientInfo::parseDOBDate($this->nullOrValue($row['dob']))
            )->toDateString(),
            'first_name'          => $this->nullOrValue($row['first_name']),
            'last_name'           => $this->nullOrValue($row['last_name']),
            'mrn'                 => $this->nullOrValue($row['mrn']),
            'primary_insurance'   => $this->nullOrValue($row['primary_insurance']),
            'secondary_insurance' => $this->nullOrValue($row['secondary_insurance']),
            'provider'            => $this->nullOrValue($row['provider']),
            'location'            => $this->nullOrValue($row['location']),
            'practice_id'         => $this->practiceId,
        ];

        $args['location_id'] = optional(
            Location::where('practice_id', $args['practice_id'])->where(
                'name',
                $args['location']
            )->first()
        )->id;

        $args['billing_provider_user_id'] = optional(
            CcdaImporterWrapper::searchBillingProvider($args['provider'], $args['practice_id'])
        )->id;

        return SupplementalPatientData::updateOrCreate(
            [
                'mrn'         => $args['mrn'],
                'practice_id' => $args['practice_id'],
            ],
            $args
        );
    }
}
