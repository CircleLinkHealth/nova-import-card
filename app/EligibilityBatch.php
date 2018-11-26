<?php

namespace App;

class EligibilityBatch extends BaseModel
{
    const TYPE_GOOGLE_DRIVE_CCDS = 'google_drive_ccds';
    const TYPE_PHX_DB_TABLES = 'phoenix_heart_db_tables';
    const TYPE_ONE_CSV = 'one_csv';
    const ATHENA_API = 'athena_csv';
    const CLH_MEDICAL_RECORD_TEMPLATE = 'clh_medical_record_template';

    const REPROCESS_SAFE = 'safe';
    const REPROCESS_FROM_SCRATCH = 'from_scratch';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options'          => 'array',
        'stats'            => 'array',
        'validation_stats' => 'array',
    ];

    protected $fillable = [
        'practice_id',
        'type',
        'options',
        'stats',
        'status',
        'initiator_id',
    ];

    protected $attributes = [
        'status' => 0,
    ];

    public function incrementEligibleCount()
    {
        $this->incrementCount('eligible');
    }

    public function incrementIneligibleCount()
    {
        $this->incrementCount('ineligible');
    }

    public function incrementErrorCount()
    {
        $this->incrementCount('errors');
    }

    public function incrementDuplicateCount()
    {
        $this->incrementCount('duplicates');
    }

    private function incrementCount($key)
    {
        $stats = $this->stats;

        $stats[$key] = $this->stats[$key] + 1;

        $this->stats = $stats;
    }

    public function getStatus($statusId = null)
    {
        if ( ! $statusId) {
            if (is_null($this->status)) {
                return null;
            }
            $statusId = $this->status;
        }


        foreach (self::STATUSES as $name => $id) {
            if ($id == $statusId) {
                return $name;
            }
        }

        return null;
    }

    public function isCompleted()
    {
        return $this->getStatus() === 'complete';
    }

    public function getStatusFontColor()
    {
        switch ($this->status) {
            case 0:
                return 'grey';
                break;
            case 1:
                return 'blue';
                break;
            case 2:
                return 'red';
                break;
            case 3:
                return 'green';
                break;
        }
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function eligibilityJobs()
    {
        return $this->hasMany(EligibilityJob::class, 'batch_id');
    }

    /**
     * @return bool
     */
    public function hasJobs(): bool
    {
        return $this->eligibilityJobs()->count() > 0;
    }

    /**
     * @return bool
     */
    public function shouldSafeReprocess()
    {
        return $this->options['reprocessingMethod'] ?? '' == self::REPROCESS_SAFE;
    }

    /**
     * Return a link to view this batch's status
     *
     * @return null|string
     */
    public function linkToView()
    {
        if ( ! $this->id) {
            return null;
        }

        return route('eligibility.batch.show', [$this->id]);
    }

    public function getOutcomes()
    {
        return EligibilityJob::selectRaw('count(*) as total, outcome')
                             ->where('batch_id', $this->id)
                             ->groupBy('outcome')
                             ->get()
                             ->mapWithKeys(function ($result) {
                                 return [
                                     is_null($result['outcome'])
                                         ? 'Not processed yet.'
                                         : $result['outcome'] => $result['total'],
                                 ];
                             });
    }

    public function initiatorUser()
    {
        return $this->hasOne(User::class, 'id', 'initiator_id');
    }

    public function getValidationStats()
    {

        $structure = EligibilityJob::selectRaw('count(*) as total, invalid_structure')
                                   ->where('batch_id', $this->id)
                                   ->groupBy('invalid_structure')
                                   ->get()
                                   ->mapWithKeys(function ($result) {
                                       return [$result['invalid_structure'] => $result['total']];
                                   });

        $data = EligibilityJob::selectRaw('count(*) as total, invalid_data')
                              ->where('batch_id', $this->id)
                              ->groupBy('invalid_data')
                              ->get()
                              ->mapWithKeys(function ($result) {
                                  return [$result['invalid_data'] => $result['total']];
                              });

        $mrn = EligibilityJob::selectRaw('count(*) as total, invalid_mrn')
                             ->where('batch_id', $this->id)
                             ->groupBy('invalid_mrn')
                             ->get()
                             ->mapWithKeys(function ($result) {
                                 return [$result['invalid_mrn'] => $result['total']];
                             });

        $firstName = EligibilityJob::selectRaw('count(*) as total, invalid_first_name')
                                   ->where('batch_id', $this->id)
                                   ->groupBy('invalid_first_name')
                                   ->get()
                                   ->mapWithKeys(function ($result) {
                                       return [$result['invalid_first_name'] => $result['total']];
                                   });

        $lastName = EligibilityJob::selectRaw('count(*) as total, invalid_last_name')
                                  ->where('batch_id', $this->id)
                                  ->groupBy('invalid_last_name')
                                  ->get()
                                  ->mapWithKeys(function ($result) {
                                      return [$result['invalid_last_name'] => $result['total']];
                                  });


        $dob = EligibilityJob::selectRaw('count(*) as total, invalid_dob')
                             ->where('batch_id', $this->id)
                             ->groupBy('invalid_dob')
                             ->get()
                             ->mapWithKeys(function ($result) {
                                 return [$result['invalid_dob'] => $result['total']];
                             });

        $problems = EligibilityJob::selectRaw('count(*) as total, invalid_problems')
                                  ->where('batch_id', $this->id)
                                  ->groupBy('invalid_problems')
                                  ->get()
                                  ->mapWithKeys(function ($result) {
                                      return [$result['problems'] => $result['total']];
                                  });

        $phones = EligibilityJob::selectRaw('count(*) as total, invalid_phones')
                                ->where('batch_id', $this->id)
                                ->groupBy('invalid_phones')
                                ->get()
                                ->mapWithKeys(function ($result) {
                                    return [$result['phones'] => $result['total']];
                                });

        return [
            'total'             => $this->eligibilityJobs()->count(),
            'invalid_structure' => $structure->get(1) ?? 0,
            'invalid_data'      => $data->get(1) ?? 0,
            'mrn'               => $mrn->get(1) ?? 0,
            'first_name'        => $firstName->get(1) ?? 0,
            'last_name'         => $lastName->get(1) ?? 0,
            'dob'               => $dob->get(1) ?? 0,
            'problems'          => $problems->get(1) ?? 0,
            'phones'            => $phones->get(1) ?? 0,
        ];

    }
}
