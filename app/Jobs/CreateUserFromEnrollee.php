<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Repositories\UserRepository;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\SharedModels\Entities\Ccda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

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
        $newUserId = (string) Str::uuid();

        $email = empty($email = $this->enrollee->email)
            ? $newUserId.'@careplanmanager.com'
            : $email;

        //need this to determine if is_awv. What about if there is no CCDA? e.g. only has eligibility job?
        $ccda  = $this->enrollee->ccda;
        $isAwv = $ccda ? Ccda::IMPORTER_AWV === $ccda->source : false;

        //what about if enrollee already has user?
        $userCreatedFromEnrollee = (new UserRepository())->createNewUser(
            new ParameterBag(
                [
                    'email'        => $email,
                    'password'     => Str::random(30),
                    'display_name' => ucwords(
                        strtolower($this->enrollee->first_name.' '.$this->enrollee->last_name)
                    ),
                    'first_name' => $this->enrollee->first_name,
                    'last_name'  => $this->enrollee->last_name,
                    'mrn_number' => $this->enrollee->mrn,
                    'birth_date' => $this->enrollee->dob,
                    'username'   => empty($email)
                        ? $newUserId
                        : $email,
                    'program_id'        => $this->enrollee->practice_id,
                    'is_auto_generated' => true,
                    'roles'             => [$this->surveyRoleId],
                    'is_awv'            => $ccda,
                    'address'           => $this->enrollee->address,
                    'address2'          => $this->enrollee->address_2,
                    'city'              => $this->enrollee->city,
                    'state'             => $this->enrollee->state,
                    'zip'               => $this->enrollee->zip,

                    //this will be changed back to Enrolled in  Tasks\ImportPatientInfo
                    'ccm_status' => Patient::UNREACHABLE,
                ]
            )
        );

        //Are we sure we have ccda? can we implement it from importer instead?
//        $this->ccda->patient_id = $this->patient->id;

        //handle phones here, we wanna make sure enrollee cell_phone gets priority, since we are sending SMS.
        //This is worth investigating. When Zack goes through enrollees to see if they are eligible for auto enrollment, does he check phones?
        //Do we need to know anything about his validation process, to make sure we get the correct number here?
        $this->attachPhones($userCreatedFromEnrollee);

        //is this going to be a problem if it stays on during the importing phase?
        $userCreatedFromEnrollee->setBillingProviderId($this->enrollee->provider->id);

        $this->enrollee->update(['user_id' => $userCreatedFromEnrollee->id]);

        $this->updateEnrolleeSurveyStatuses(
            $this->enrollee->id,
            $userCreatedFromEnrollee->id,
            null,
            false,
            optional($userCreatedFromEnrollee->patientInfo)->id
        );

        event(new AutoEnrollableCollected([$userCreatedFromEnrollee->id], false, $this->color));
    }

    private function attachPhones(User $userCreatedFromEnrollee)
    {
        //Self Enrollment uses $user->getPhone() - If there are many Primary Phone Numbers, it will get the one created first
        //1st prio - cell phone
        $cellPhone = $this->enrollee->cell_phone_e164;
        if ( ! empty($cellPhone)) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::MOBILE,
                'number'     => $cellPhone,
                'is_primary' => true,
            ]);
        }

        //2nd prio - primary phone - will likely be empty
        $primaryPhone = $this->enrollee->primary_phone_e164;
        if ($primaryPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $primaryPhone,
                'is_primary' => true,
            ]);
        }

        //3rd prio - home phone
        $homePhone = $this->enrollee->home_phone_e164;
        if ($homePhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $homePhone,
                'is_primary' => true,
            ]);
        }

        //last
        $otherPhone = $this->enrollee->other_phone_e164;
        if ($otherPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'number'     => $otherPhone,
                'is_primary' => false,
            ]);
        }
    }
}
