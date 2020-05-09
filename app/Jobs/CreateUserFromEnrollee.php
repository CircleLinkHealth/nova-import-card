<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUserFromEnrollee implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var null
     */
    private $color;

    /**
     * @var Enrollee
     */
    private $enrollee;
    private $surveyRoleId;

    /**
     * Create a new job instance.
     *
     * @param $surveyRoleId
     * @param null $color
     */
    public function __construct(Enrollee $enrollee, $surveyRoleId, $color = null)
    {
        $this->enrollee     = $enrollee;
        $this->surveyRoleId = $surveyRoleId;
        $this->color        = $color;
    }

    /**
     * Execute the job.
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|User
     */
    public function handle()
    {
        $surveyRoleId = $this->surveyRoleId;
        //                Create User model from enrollee
        /** @var User $userCreatedFromEnrollee */
        $userCreatedFromEnrollee = $this->enrollee->user()->updateOrCreate(
            [
                'email' => $this->enrollee->email,
            ],
            [
                'program_id'      => $this->enrollee->practice_id,
                'display_name'    => $this->enrollee->first_name.' '.$this->enrollee->last_name,
                'user_registered' => Carbon::parse(now())->toDateTimeString(),
                'first_name'      => $this->enrollee->first_name,
                'last_name'       => $this->enrollee->last_name,
                'address'         => $this->enrollee->address,
                'address_2'       => $this->enrollee->address_2,
                'city'            => $this->enrollee->city,
                'state'           => $this->enrollee->state,
                'zip'             => $this->enrollee->zip,
            ]
        );

        $userCreatedFromEnrollee->attachGlobalRole($surveyRoleId);

        $userCreatedFromEnrollee->phoneNumbers()->create([
            'number'     => $this->enrollee->primary_phone,
            'is_primary' => true,
        ]);

        $userCreatedFromEnrollee->patientInfo()->create([
            'birth_date' => $this->enrollee->dob,
        ]);

        // Why this does not work in create query above?
        $userCreatedFromEnrollee->patientInfo->update([
            'ccm_status' => Patient::UNREACHABLE,
        ]);

        $userCreatedFromEnrollee->setBillingProviderId($this->enrollee->provider->id);
        $this->enrollee->update(['user_id' => $userCreatedFromEnrollee->id]);
        // The above can be abstracted more
        event(new AutoEnrollableCollected($userCreatedFromEnrollee, false, $this->color));
        $this->updateEnrolleeSurveyStatuses($this->enrollee->id, $userCreatedFromEnrollee->id, null);
    }
}
