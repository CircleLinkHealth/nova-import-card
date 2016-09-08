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

    public static function notifyProvidersToApproveCareplans()
    {
        $userRepo = new UserRepository();

        $providers = $userRepo->findByRole('provider');

        $emailsSent = $providers->map(function ($user) {
            $recipients = [
                $user->user_email
            ];

            $numberOfCareplans = PatientCarePlan::getNumberOfCareplansPendingApproval($user);

            if ($numberOfCareplans < 1) return false;

            $data = [
                'numberOfCareplans' => $numberOfCareplans,
                'drName' => $user->fullName,
            ];

            $view = 'emails.careplansPendingApproval';
            $subject = "{$numberOfCareplans} CircleLink Care Plans for your Approval!";


            Mail::send($view, $data, function ($message) use ($recipients, $subject) {
                $message->from('notifications@careplanmanager.com', 'CircleLink Health')
                    ->to($recipients)
                    ->subject($subject);
            });
        });
    }

}
