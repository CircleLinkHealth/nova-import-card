<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

class EligibilityBatch extends BaseModel
{
    const ATHENA_API                  = 'athena_csv';
    const CLH_MEDICAL_RECORD_TEMPLATE = 'clh_medical_record_template';
    const REPROCESS_FROM_SCRATCH      = 'from_scratch';

    const REPROCESS_SAFE = 'safe';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];
    const TYPE_GOOGLE_DRIVE_CCDS = 'google_drive_ccds';
    const TYPE_ONE_CSV           = 'one_csv';
    const TYPE_PHX_DB_TABLES     = 'phoenix_heart_db_tables';

    protected $attributes = [
        'status' => 0,
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

    public function eligibilityJobs()
    {
        return $this->hasMany(EligibilityJob::class, 'batch_id');
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

    public function getStatus($statusId = null)
    {
        if (!$statusId) {
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

    public function getValidationStats()
    {
        $structure = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_structure', 1)
            ->count();

        $data = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_data', 1)
            ->count();

        $mrn = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_mrn', 1)
            ->count();

        $firstName = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_first_name', 1)
            ->count();

        $lastName = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_last_name', 1)
            ->count();

        $dob = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_dob', 1)
            ->count();

        $problems = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_problems', 1)
            ->count();

        $phones = EligibilityJob::where('batch_id', $this->id)
            ->where('invalid_phones', 1)
            ->count();

        return [
            'total'             => $this->eligibilityJobs()->count(),
            'invalid_structure' => $structure,
            'invalid_data'      => $data,
            'mrn'               => $mrn,
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'dob'               => $dob,
            'problems'          => $problems,
            'phones'            => $phones,
        ];
    }

    /**
     * @return bool
     */
    public function hasJobs(): bool
    {
        return $this->eligibilityJobs()->count() > 0;
    }

    public function incrementDuplicateCount()
    {
        $this->incrementCount('duplicates');
    }

    public function incrementEligibleCount()
    {
        $this->incrementCount('eligible');
    }

    public function incrementErrorCount()
    {
        $this->incrementCount('errors');
    }

    public function incrementIneligibleCount()
    {
        $this->incrementCount('ineligible');
    }

    public function initiatorUser()
    {
        return $this->hasOne(User::class, 'id', 'initiator_id');
    }

    public function isCompleted()
    {
        return 'complete' === $this->getStatus();
    }

    /**
     * Return a link to view this batch's status.
     *
     * @return string|null
     */
    public function linkToView()
    {
        if (!$this->id) {
            return null;
        }

        return route('eligibility.batch.show', [$this->id]);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    /**
     * @return bool
     */
    public function shouldSafeReprocess()
    {
        return $this->options['reprocessingMethod'] ?? '' == self::REPROCESS_SAFE;
    }

    private function incrementCount($key)
    {
        $stats = $this->stats;

        $stats[$key] = $this->stats[$key] + 1;

        $this->stats = $stats;
    }
}
