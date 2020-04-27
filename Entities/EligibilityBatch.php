<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Jobs\ProcessSinglePatientEligibility;

/**
 * CircleLinkHealth\Eligibility\Entities\EligibilityBatch.
 *
 * @property int                                                                                              $id
 * @property int|null                                                                                         $initiator_id
 * @property int|null                                                                                         $practice_id
 * @property string                                                                                           $type
 * @property int                                                                                              $status
 * @property array                                                                                            $options
 * @property array                                                                                            $stats
 * @property \Illuminate\Support\Carbon|null                                                                  $created_at
 * @property \Illuminate\Support\Carbon|null                                                                  $updated_at
 * @property string|null                                                                                      $deleted_at
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityJob[]|\Illuminate\Database\Eloquent\Collection $eligibilityJobs
 * @property \CircleLinkHealth\Customer\Entities\User                                                         $initiatorUser
 * @property \CircleLinkHealth\Customer\Entities\Practice|null                                                $practice
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection      $revisionHistory
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch newModelQuery()
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch newQuery()
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch query()
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereCreatedAt($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereDeletedAt($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereId($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereInitiatorId($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereOptions($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch wherePracticeId($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereStats($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereStatus($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereType($value)
 * @method   static                                                                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Eligibility\Entities\EligibilityBatch whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $eligibility_jobs_count
 * @property int|null $revision_history_count
 */
class EligibilityBatch extends BaseModel
{
    const ATHENA_API                  = 'athena_csv';
    const CLH_MEDICAL_RECORD_TEMPLATE = 'clh_medical_record_template';

    const OUTCOME_NOT_PROCESSED_YET = 'Not processed yet.';
    const REPROCESS_FROM_SCRATCH    = 'from_scratch';

    const REPROCESS_SAFE = 'safe';
    /**
     * A batch that is always open to add more ptients to.
     */
    const RUNNING = 'running';

    const STATUSES = [
        'not_started'     => 0,
        'processing'      => 1,
        'error'           => 2,
        'complete'        => 3,
        'runs_infinitely' => 4,
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
                if (is_null($result['outcome'])) {
                    $outcome = [self::OUTCOME_NOT_PROCESSED_YET => $result['total'] ?? null];
                } elseif (EligibilityJob::ELIGIBLE === $result['outcome']) {
                    $outcome = ['eligible_and_not_in_cpm' => $result['total']];
                } else {
                    $outcome = [$result['outcome'] => $result['total']];
                }

                return $outcome ?? [];
            });
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

    public function getType()
    {
        switch ($this->type) {
            case self::ATHENA_API:
                return 'Athena';
                break;
            case self::TYPE_ONE_CSV:
                return 'CSV';
                break;
            case self::TYPE_GOOGLE_DRIVE_CCDS:
                return 'CCDs in GoogleDrive';
                break;
            case self::CLH_MEDICAL_RECORD_TEMPLATE:
                return 'CLH Template';
                break;
            default:
                return '';
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

    public function hasJobs(): bool
    {
        return $this->eligibilityJobs()->exists();
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

    public function isFinishedFetchingFiles()
    {
        return array_key_exists('numberOfFiles', $this->options) && (int) $this->options['numberOfFiles'] === (int) $this->eligibilityJobs()->count();
    }

    /**
     * Return a link to view this batch's status.
     *
     * @return string|null
     */
    public function linkToView()
    {
        if ( ! $this->id) {
            return null;
        }

        return route('eligibility.batch.show', [$this->id]);
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function processPendingJobs($pageSize = 100, $onQueue = 'low')
    {
        $this->eligibilityJobs()
            ->where('status', '=', 0)
            ->orWhere([
                ['status', '=', 1],
                ['updated_at', '>', now()->subMinutes(10)],
            ])
            ->chunkById($pageSize, function ($ejs) use ($onQueue) {
                $ejs->each(function ($job) use ($onQueue) {
                    ProcessSinglePatientEligibility::dispatch(
                        $job,
                        $this,
                        $this->practice
                    )->onQueue($onQueue);
                });
            });
    }

    /**
     * Get the Practice's "running batch", or create one if it does not exist.
     * A "running batch" is always open, and its purpose is to attach medical records when they are sent to us one by one instead of in a batch.
     */
    public static function runningBatch(Practice $practice)
    {
        return EligibilityBatch::firstOrCreate([
            'type'        => self::RUNNING,
            'practice_id' => $practice->id,
        ], [
            'status'  => EligibilityBatch::STATUSES['runs_infinitely'],
            'options' => [
                'filterLastEncounter' => false,
                'filterInsurance'     => false,
                'filterProblems'      => true,
            ],
        ]);
    }

    public function shouldFilterInsurance()
    {
        return array_key_exists('filterInsurance', $this->options) ? (bool) $this->options['filterInsurance'] : false;
    }

    public function shouldFilterLastEncounter()
    {
        return array_key_exists('filterLastEncounter', $this->options) ? (bool) $this->options['filterLastEncounter'] : false;
    }

    public function shouldFilterProblems()
    {
        return array_key_exists('filterProblems', $this->options) ? (bool) $this->options['filterProblems'] : true;
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
