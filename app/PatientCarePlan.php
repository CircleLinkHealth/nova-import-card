<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model {

    protected $guarded = [];

    public static function getNumberOfCareplansPendingApproval(User $user)
    {
        $pendingApprovals = 0;

        // patient approval counts
        if ($user->hasRole(['administrator', 'care-center'])) {
            // care-center and administrator counts number of drafts
            $pendingApprovals = PatientInfo::whereIn('user_id', $user->viewablePatientIds())
                ->whereCareplanStatus('draft')
                ->count();
        } else if ($user->hasRole(['provider'])) {
            // provider counts number of drafts
            $pendingApprovals = PatientInfo::whereCareplanStatus('qa_approved')
                ->whereCcmStatus('enrolled')
                ->whereHas('user.patientCareTeamMembers', function ($q) use ($user){
                    $q->where('member_user_id', '=', $user->id)
                        ->where('type', '=', PatientCareTeamMember::BILLING_PROVIDER);
                })
                ->count();
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
