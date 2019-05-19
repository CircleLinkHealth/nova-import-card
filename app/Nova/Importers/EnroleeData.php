<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Enrollee;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
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

        $provider = User::search($row['provider'])->first();

        if ( ! $provider) {
            return;
        }

        $row['provider'] = $provider->id;
        $row['practice'] = optional($this->practice)->id;

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
        $fileName = array_key_exists('file', $_FILES)
            ? $_FILES['file']['name']
            : null;

        if ($fileName) {
            $array = explode('.', $fileName);

            return Practice::search($array[0])->first();
        }

        //throw Exception
        return null;
    }
}
