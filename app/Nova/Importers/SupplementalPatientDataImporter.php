<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Importers;

use App\EligiblePatientView;
use App\Nova\Actions\ReimportCcda;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Eligibility\CcdaImporter\CcdaImporterWrapper;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class SupplementalPatientDataImporter implements ToCollection, WithChunkReading, WithValidation, WithHeadingRow
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
            'first_name'          => $this->nullOrValue($row['first_name']),
            'last_name'           => $this->nullOrValue($row['last_name']),
            'mrn'                 => $this->nullOrValue($row['mrn']),
            'primary_insurance'   => $this->nullOrValue($row['primary_insurance']),
            'secondary_insurance' => $this->nullOrValue($row['secondary_insurance']),
            'provider'            => $this->nullOrValue($row['provider']),
            'location'            => $this->nullOrValue($row['location']),
            'practice_id'         => $this->getPractice()->id,
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

        if ( ! empty($args['mrn']) && ! empty($args['first_name']) && ! empty($args['last_name'])) {
            return tap(
                SupplementalPatientData::updateOrCreate(
                    [
                        'mrn'         => $row['mrn'],
                        'practice_id' => $this->getPractice()->id,
                    ],
                    $args
                ),
                function ($spd) use ($row) {
                    if ( ! array_key_exists('import_now', $row)) {
                        return $spd;
                    }

                    if ( ! ('y' === strtolower(
                        $row['import_now']
                    ) && $spd->practice_id && $spd->first_name && $spd->last_name && $spd->mrn)) {
                        return $spd;
                    }

                    $query = Enrollee::with(
                        [
                            'ccda' => function ($q) {
                                $q->select(['id', 'location_id', 'practice_id', 'billing_provider_id', 'patient_id']);
                            },
                        ]
                    );

                    if ( ! ($enrollee = $query->where('practice_id', $spd->practice_id)->where(
                        'first_name',
                        $spd->first_name
                    )->where('last_name', $spd->last_name)->where('mrn', $spd->mrn)->first())) {
                        $jobId = EligiblePatientView::where('mrn', $spd->mrn)->where(
                            'last_name',
                            $spd->last_name
                        )->where('first_name', $spd->first_name)->where('practice_id', $spd->practice_id)->value(
                            'eligibility_job_id'
                        );

                        if ( ! $jobId) {
                            return $spd;
                        }

                        $enrollee = $query->firstOrNew(
                            [
                                'practice_id' => $spd->practice_id,
                                'first_name'  => $spd->first_name,
                                'last_name'   => $spd->last_name,
                                'mrn'         => $spd->mrn,
                            ],
                            [
                                'eligibility_job_id' => $jobId,
                                'dob'                => $spd->dob,
                            ]
                        );
                    }

                    $ccda = $enrollee->ccda;

                    if ( ! $ccda) {
                        $ccda = Ccda::where('practice_id', $spd->practice_id)->where(
                            'patient_mrn',
                            $spd->mrn
                        )->where('patient_last_name', $spd->last_name)->select(
                            ['id', 'location_id', 'practice_id', 'billing_provider_id', 'patient_id']
                        )->first();
                        if ($ccda) {
                            $enrollee->medical_record_id = $ccda->id;
                        }
                    }

                    if ( ! $enrollee->location_id && $spd->location_id) {
                        $enrollee->location_id = $spd->location_id;
                    }

                    if ( ! $enrollee->provider_id && $spd->billing_provider_user_id) {
                        $enrollee->provider_id = $spd->billing_provider_user_id;
                    }

                    if ($ccda && ! $enrollee->user_id && $ccda->patient_id) {
                        $enrollee->user_id = $ccda->patient_id;
                    }

                    if ($enrollee->isDirty()) {
                        $enrollee->save();
                    }

                    if ($ccda && ! $ccda->location_id && $spd->location_id) {
                        $ccda->location_id = $spd->location_id;
                    }

                    if ($ccda && ! $ccda->billing_provider_id && $spd->billing_provider_user_id) {
                        $ccda->billing_provider_id = $spd->billing_provider_user_id;
                    }

                    if (optional($ccda)->isDirty()) {
                        $ccda->save();
                    }

                    if ($enrollee->user_id) {
                        ReimportCcda::for($enrollee->user_id, auth()->id());

                        return;
                    }

                    return ImportConsentedEnrollees::dispatch([$enrollee->id]);
                }
            );
        }
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
