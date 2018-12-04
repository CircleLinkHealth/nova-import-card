<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/07/2017
 * Time: 12:32 PM
 */

namespace App\Repositories;

use App\CareplanAssessment;

class CareplanAssessmentRepository
{
    public function model()
    {
        return app(CareplanAssessment::class);
    }

    public function assessments($userId = null)
    {
        if (!$userId) {
            return $this->model()->paginate();
        } else {
            return $this->model()->where([ 'careplan_id' => $userId ])->get();
        }
    }

    public function editKeyTreatment($userId, $approverId, $keyTreatment)
    {
        $model = $this->model()->where([ 'careplan_id' => $userId ]);
        $assessment = $model->firstOrNew([]);
        $assessment->key_treatment = $keyTreatment;
        $assessment->careplan_id = $userId;
        if (!$assessment->provider_approver_id) {
            $assessment->provider_approver_id = $approverId;
        }
        $assessment->save();
        return $model->first();
    }
}
