<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model
{

    protected $guarded = [];

    public static function getNumberOfCareplansPendingApproval(User $user)
    {
        $pendingApprovals = 0;

        if ($user->hasRole([
            'administrator',
            'care-center',
        ])
        ) {
            $pendingApprovals = User::ofType('participant')
                ->intersectPracticesWith($user)
                ->whereHas('patientInfo', function ($q) {
                    $q->whereCareplanStatus('draft');
                })
                ->count();
        } else {
            if ($user->hasRole(['provider'])) {
                $pendingApprovals = User::ofType('participant')
                    ->intersectPracticesWith($user)
                    ->whereHas('patientInfo', function ($q) {
                        $q->whereCareplanStatus('qa_approved')
                            ->whereCcmStatus('enrolled');
                    })
                    ->whereHas('patientCareTeamMembers', function ($q) use
                    (
                        $user
                    ) {
                        $q->where('member_user_id', '=', $user->id)
                            ->where('type', '=', PatientCareTeamMember::BILLING_PROVIDER);
                    })
                    ->count();
            }
        }

        return $pendingApprovals;
    }

    public function carePlanTemplate()
    {
        return $this->belongsTo(CarePlanTemplate::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function getCarePlanTemplateIdAttribute()
    {
        //@todo: pretty sure that's not the way it's done. come back here later
        return $this->attributes['care_plan_template_id'];
    }
}
