<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\BelongsToCcda;
use CircleLinkHealth\Eligibility\Factories\AthenaEligibilityCheckableFactory;
use CircleLinkHealth\SharedModels\Entities\Enrollee;

/**
 * CircleLinkHealth\Eligibility\Entities\TargetPatient.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $batch_id
 * @property string|null                                                                                 $eligibility_job_id
 * @property int                                                                                         $ehr_id
 * @property int|null                                                                                    $user_id
 * @property int|null                                                                                    $enrollee_id
 * @property int                                                                                         $ehr_patient_id
 * @property int                                                                                         $ehr_practice_id
 * @property int                                                                                         $ehr_department_id
 * @property string|null                                                                                 $status
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property string                                                                                      $description
 * @property \CircleLinkHealth\Customer\Entities\Ehr                                                     $ehr
 * @property \CircleLinkHealth\SharedModels\Entities\Enrollee|null                                        $enrollee
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $user
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereBatchId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereDescription($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrDepartmentId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPatientId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEhrPracticeId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEligibilityJobId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereEnrolleeId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereStatus($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUpdatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereUserId($value)
 * @mixin \Eloquent
 * @property int                                                          $practice_id
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityBatch|null $batch
 * @property \CircleLinkHealth\Customer\Entities\Practice                 $practice
 * @method   static                                                       \Illuminate\Database\Eloquent\Builder|\App\TargetPatient wherePracticeId($value)
 * @property int|null                                                     $ccda_id
 * @property \CircleLinkHealth\SharedModels\Entities\Ccda|null            $ccda
 * @method   static                                                       \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereCcdaId($value)
 * @property int|null                                                     $revision_history_count
 * @property int                                                          $department_id
 * @method   static                                                       \Illuminate\Database\Eloquent\Builder|\App\TargetPatient whereDepartmentId($value)
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityJob|null   $eligibilityJob
 */
class TargetPatient extends BaseModel
{
    use BelongsToCcda;

    const STATUS_CONSENTED  = 'consented';
    const STATUS_DUPLICATE  = 'duplicate';
    const STATUS_ELIGIBLE   = 'eligible';
    const STATUS_ENROLLED   = 'enrolled';
    const STATUS_ERROR      = 'error';
    const STATUS_INELIGIBLE = 'ineligible';
    const STATUS_TO_PROCESS = 'to_process';

    protected $fillable = [
        'ccda_id',
        'practice_id',
        'batch_id',
        'eligibility_job_id',
        'ehr_id',
        'user_id',
        'enrollee_id',
        'ehr_patient_id',
        'ehr_practice_id',
        'ehr_department_id',
        'status',
        'description',
    ];

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class, 'batch_id');
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class, 'ehr_id');
    }

    public function eligibilityJob()
    {
        return $this->belongsTo(EligibilityJob::class);
    }

    public function enrollee()
    {
        return $this->belongsTo(Enrollee::class, 'enrollee_id');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    /**
     * @throws \Exception
     *
     * @return EligibilityJob
     */
    public function processEligibility()
    {
        $this->loadMissing('batch');

        if ( ! $this->batch) {
            throw new \Exception('A batch is necessary to process a target patient.');
        }

        return tap(
            app(AthenaEligibilityCheckableFactory::class)
                ->makeAthenaEligibilityCheckable($this)
                ->createAndProcessEligibilityJobFromMedicalRecord(),
            function (EligibilityJob $eligibilityJob) {
                $this->setStatusFromEligibilityJob($eligibilityJob);
                $this->eligibility_job_id = $eligibilityJob->id;
                $this->save();
            }
        );
    }

    public function setStatusFromEligibilityJob(EligibilityJob $eligibilityJob)
    {
        if ($eligibilityJob->isIneligible()) {
            $this->status = self::STATUS_INELIGIBLE;
        } elseif ($eligibilityJob->isEligible() || $eligibilityJob->wasAlreadyFoundEligibleInAPreviouslyCreatedBatch()) {
            $this->status = self::STATUS_ELIGIBLE;
        } elseif ($eligibilityJob->isAlreadyEnrolled()) {
            $this->status = self::STATUS_ENROLLED;
        }
    }

    public function setStatusFromException(\Exception $e)
    {
        $this->status      = TargetPatient::STATUS_ERROR;
        $this->description = $e->getMessage();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
