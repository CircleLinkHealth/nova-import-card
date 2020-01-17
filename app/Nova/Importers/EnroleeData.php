<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use CircleLinkHealth\Eligibility\Entities\Enrollee;
use App\Search\PracticeByName;
use App\Search\ProviderByName;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class EnroleeData implements OnEachRow, WithChunkReading, WithValidation, WithHeadingRow
{
    use Importable;

    protected $attributes;

    protected $modelClass;

    protected $practice;

    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
        $this->practice   = $this->getPractice();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 200;
    }

    /**
     * @param Row $row
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();

        $provider = ProviderByName::first($row['provider']);

        $row['provider_id'] = optional($provider)->id;
        $row['practice_id'] = optional($this->practice)->id;

        Enrollee::updateOrCreate(
            [
                'mrn' => $row['mrn'],
            ],
            $row
        );
    }

    public function rules(): array
    {
        return $this->rules;
    }

    protected function getPractice()
    {
        $fileName = request()->file->getClientOriginalName();

        if ($fileName) {
            $array = explode('.', $fileName);

            $practice = PracticeByName::first($array[0]);

            if ( ! $practice) {
                throw new \Exception('Practice not found. Please make sure that the file name is a valid Practice name.', 500);
            }

            return $practice;
        }

        throw new \Exception('Something went wrong. File not found.', 500);
    }
}
