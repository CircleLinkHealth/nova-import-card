<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPhones;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * IMPORTANT!!!
 *
 * THIS CLASS
 *
 * IS MEANT TO BE USED
 *
 * TEMPORARILY.
 *
 * PLEASE DO NOT USE IT FOR ANY OTHER PURPOSE
 *
 * Class PatientContactPreferencesImporter
 */
class PatientContactPreferencesImporter implements ToCollection, WithChunkReading, WithValidation, WithHeadingRow
{
    use Importable;

    protected $attributes;

    protected $modelClass;
    protected $resource;

    protected $rules;

    public function __construct($resource, $attributes, $rules, $modelClass)
    {
        $this->resource   = $resource;
        $this->attributes = $attributes;
        $this->rules      = $rules;
        $this->modelClass = $modelClass;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function collection(Collection $rows)
    {
        $dobs = collect([]);

        //DB transaction will revert once an exception is thrown.
        DB::transaction(
            function () use ($rows, &$dobs) {
                foreach ($rows as $row) {
                    $row = $row->toArray();

                    //accept null dobs
                    if ($row['dob']) {
                        $date = $this->validateDob($row['dob']);

                        if ( ! $date) {
                            throw new \Exception("Invalid date {$row['dob']}");
                        }

                        $dobs->push(['dateString' => $date->toDateString()]);

                        if (10 === $dobs->count()) {
                            //check if Carbon is parsing dates all as the same date
                            if (10 === $dobs->where('dateString', $date->toDateString())->count()) {
                                throw new \Exception('Something went wrong while parsing patient dates of birth.');
                            }
                            //reset collection
                            $dobs = collect([]);
                        }

                        $row['dob'] = $date;
                    }

                    $this->persistRow($row);
                }
            }
        );
    }

    public function getPractice(): Practice
    {
        return $this->resource->fields->getFieldValue('practice');
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
        $this->persistRow($row->toArray());
    }

    public function rules(): array
    {
        return $this->rules;
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

    private function parsePhones(?string $phones, array &$args)
    {
        if (empty($phones)) {
            return;
        }
        if (\Illuminate\Support\Str::contains($phones, 'or')) {
            $phones = explode('or', $phones);

            if (ImportPhones::validatePhoneNumber($phones[0])) {
                $args['cell_phone'] = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumberE164($phones[0]);
            }

            if (ImportPhones::validatePhoneNumber($phones[1])) {
                $args['home_phone'] = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumberE164($phones[1]);
            }

            return;
        }

        if (ImportPhones::validatePhoneNumber($phones)) {
            $args['primary_phone'] = (new \CircleLinkHealth\Core\StringManipulation())->formatPhoneNumberE164($phones);

            return;
        }

        if (\Illuminate\Support\Str::contains($phones, ',')) {
            $phones = explode(',', $phones);

            foreach ($phones as $phone) {
                if (\Illuminate\Support\Str::contains(strtolower($phone), 'cell') && ImportPhones::validatePhoneNumber($p = formatPhoneNumberE164($phone))) {
                    $args['cell_phone'] = $p;
                    continue;
                }

                if (\Illuminate\Support\Str::contains(strtolower($phone), 'home') && ImportPhones::validatePhoneNumber($p = formatPhoneNumberE164($phone))) {
                    $args['home_phone'] = $p;
                    continue;
                }
            }

            return;
        }
    }

    private function persistRow(array $row)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $args = [
            'dob' => optional(
                ImportPatientInfo::parseDOBDate($this->nullOrValue($row['dob']))
            )->toDateString(),
            'first_name'       => $this->nullOrValue($row['first_name']),
            'last_name'        => $this->nullOrValue($row['last_name']),
            'mrn'              => $this->nullOrValue($row['mrn']),
            'other_note'       => $this->nullOrValue($row['comments']),
            'preferred_window' => $this->nullOrValue($row['preferred_call_time']),
            'preferred_days'   => $this->nullOrValue($row['preferred_call_days']),
            'location'         => $this->nullOrValue($row['location']),
            'practice_id'      => $this->getPractice()->id,
        ];

        $this->parsePhones($this->nullOrValue($row['preferred_contact']), $args);

        $args['location_id'] = optional(
            Location::where('practice_id', $args['practice_id'])->where(
                'name',
                $args['location']
            )->first()
        )->id;

        $enrolee = Enrollee::updateOrCreate(
            [
                'dob'         => $args['dob'],
                'first_name'  => $args['first_name'],
                'last_name'   => $args['last_name'],
                'practice_id' => $this->getPractice()->id,
            ],
            $args
        );
    }

    private function validateDob($dob)
    {
        if ( ! $dob) {
            return false;
        }

        $validator = \Validator::make(['dob' => $dob], ['dob' => 'required|filled|date']);

        if ($validator->fails()) {
            return false;
        }

        try {
            $date = Carbon::parse($dob);

            if ($date->isToday()) {
                return false;
            }

            return $this->correctCenturyIfNeeded($date);
        } catch (\InvalidArgumentException $e) {
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
