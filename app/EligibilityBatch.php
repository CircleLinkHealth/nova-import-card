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
        'options'           => 'array',
        'stats'             => 'array',
        'validation_stats' => 'array',
    ];

    protected $fillable = [
        'practice_id',
        'type',
        'options',
        'stats',
        'status',
        'initiator_id',
        'invalid_structure',
        'validation_stats',
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

    public function getInitiatorUser()
    {
        return User::find($this->initiator_id);
    }

    public function getValidationStats()
    {
        if ($this->type == EligibilityBatch::TYPE_ONE_CSV){
            $jobs = $this->eligibilityJobs;

            $validationStats = [
                'total'             => $jobs->count(),
                'invalid_structure' => $this->invalid_structure,
                'invalid_data'      => 0,
                'mrn'               => 0,
                'name'              => 0,
                'dob'               => 0,
                'problems'          => 0,
                'phones'            => 0,
            ];
            foreach ($jobs as $job) {
                $errors = $job->errors;
                if ($errors) {
                    $validationStats['invalid_data'] += 1;
                    if (array_key_exists('mrn', $errors)) {
                        $validationStats['mrn'] += 1;
                    }

                    if (array_key_exists('first_name', $errors) || array_key_exists('last_name', $errors)) {
                        $validationStats['name'] += 1;
                    }

                    if (array_key_exists('dob', $errors)) {
                        $validationStats['dob'] += 1;
                    }
                    if (array_key_exists('problems', $errors)) {
                        $validationStats['problems'] += 1;
                    }
                    if (array_key_exists('phones', $errors)) {
                        $validationStats['phones'] += 1;
                    }
                }
            }

            return $validationStats;
        }
        if ($this->type == EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE){
            $validationStats = $this->validation_stats;
            if ($validationStats == null){
                $validationStats = [
                    'total' => 0,
                    'invalid_data' => 0,
                    'mrn'  => 0,
                    'name' => 0,
                    'dob'  => 0,
                    'problems' => 0,
                    'phones' => 0,
                ];
            }
            $validationStats['invalid_structure'] = $this->invalid_structure;
            return $validationStats;
        }

    }
}
