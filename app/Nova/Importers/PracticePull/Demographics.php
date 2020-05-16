<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers\PracticePull;

use Carbon\Carbon;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Demographics implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue
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

    public function model(array $row)
    {
        return new \App\Models\PracticePull\Demographics([
            'practice_id'             => $this->practiceId,
            'mrn'                     => $this->nullOrValue($row['mrn']),
            'first_name'              => $this->nullOrValue($row['first_name']),
            'last_name'               => $this->nullOrValue($row['last_name']),
            'last_encounter'          => Carbon::parse($row['last_encounter']),
            'dob'                     => ImportPatientInfo::parseDOBDate($this->nullOrValue($row['dob'])),
            'gender'                  => $this->nullOrValue($row['gender']),
            'lang'                    => $this->nullOrValue($row['lang']),
            'referring_provider_name' => $this->nullOrValue($row['referring_provider_name']),
            'cell_phone'              => $this->nullOrValue($row['cell_phone']),
            'home_phone'              => $this->nullOrValue($row['home_phone']),
            'other_phone'             => $this->nullOrValue($row['other_phone']),
            'primary_phone'           => $this->nullOrValue($row['primary_phone']),
            'email'                   => $this->nullOrValue($row['email']),
            'street'                  => $this->nullOrValue($row['street']),
            'street2'                 => $this->nullOrValue($row['street2']),
            'city'                    => $this->nullOrValue($row['city']),
            'state'                   => $this->nullOrValue($row['state']),
            'zip'                     => $this->nullOrValue($row['zip']),
            'primary_insurance'       => $this->nullOrValue($row['primary_insurance']),
            'secondary_insurance'     => $this->nullOrValue($row['secondary_insurance']),
            'tertiary_insurance'      => $this->nullOrValue($row['tertiary_insurance']),
        ]);
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

    public function rules(): array
    {
        return $this->rules;
    }
}
