<?php namespace App;

use App\CLH\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Model;

class PatientCarePlan extends Model {

    protected $guarded = [];

    public function carePlanTemplate()
    {
        return $this->belongsTo(CarePlanTemplate::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class,'patient_id');
    }

    public function getCarePlanTemplateIdAttribute()
    {
        //@todo: pretty sure that's not the way it's done. come back here later
        return $this->attributes['care_plan_template_id'];
    }

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
            $pendingApprovals = PatientInfo::whereIn('user_id', $user->viewablePatientIds())
                ->whereCareplanStatus('qa_approved')
                ->count();
        }

        return $pendingApprovals;
    }
}
