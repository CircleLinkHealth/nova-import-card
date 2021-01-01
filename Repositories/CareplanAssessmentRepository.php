<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Repositories;

use CircleLinkHealth\SharedModels\Entities\CareplanAssessment;

class CareplanAssessmentRepository
{
    public function assessments($userId = null)
    {
        if ( ! $userId) {
            return $this->model()->paginate();
        }

        return $this->model()->where(['careplan_id' => $userId])->get();
    }

    public function editKeyTreatment($userId, $approverId, $keyTreatment)
    {
        $model                     = $this->model()->where(['careplan_id' => $userId]);
        $assessment                = $model->firstOrNew([]);
        $assessment->key_treatment = $keyTreatment;
        $assessment->careplan_id   = $userId;
        if ( ! $assessment->provider_approver_id) {
            $assessment->provider_approver_id = $approverId;
        }
        $assessment->save();

        return $model->first();
    }

    public function model()
    {
        return app(CareplanAssessment::class);
    }
}
