<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\Search\ProviderByName;
use Carbon\Carbon;
use CircleLinkHealth\Core\StringManipulation;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Enrollees implements WithChunkReading, ToModel, WithHeadingRow, ShouldQueue, WithEvents
{
    use Importable;
    use RegistersEventListeners;

    protected $attributes;

    protected $modelClass;

    protected $resource;

    protected $rules;

    /**
     * @var int
     */
    private $practiceId;

    public function __construct(int $practiceId)
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '200M');
        ini_set('max_input_time', 900);
        ini_set('max_execution_time', 900);

        $this->practiceId = $practiceId;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function message(): string
    {
        return 'File queued for importing.';
    }

    public function model(array $row)
    {
        //not sure if we should accept null dobs
        if ($row['dob']) {
            if (is_int($row['dob'])) {
                $row['dob'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dob']);
            }

            $date = $this->validateDob($row['dob']);

            $row['dob'] = $date;
        }
        $provider = ProviderByName::first($row['provider']);

        $row['provider_id'] = optional($provider)->id;
        $row['practice_id'] = $this->practiceId;

        Enrollee::updateOrCreate(
            [
                'mrn' => $row['mrn'],
                //adding this as extra validation
                'practice_id' => $this->practiceId,
            ],
            [
                'first_name' => ucfirst(strtolower($row['first_name'])),
                'last_name'  => ucfirst(strtolower($row['last_name'])),

                'provider_id' => optional($provider)->id,

                'address'   => ucwords(strtolower($row['address'])),
                'address_2' => ucwords(strtolower($row['address_2'])),
                //we only get one phone number, add as home_phone
                'home_phone' => (new StringManipulation())->formatPhoneNumberE164($row['phone_number']),
                'dob'        => optional(
                    ImportPatientInfo::parseDOBDate($row['dob'])
                )->toDateString(),
                'city'  => ucwords(strtolower($row['city'])),
                'state' => $row['state'],
                'zip'   => $row['zip'],

                'status' => Enrollee::TO_CALL,
                'source' => Enrollee::UPLOADED_CSV,
            ]
        );
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
            if (Str::contains($dob, '/')) {
                $delimiter = '/';
            } elseif (Str::contains($dob, '-')) {
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
