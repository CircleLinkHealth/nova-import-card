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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;

class CreateUsersFromEnrollees implements ShouldQueue
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
    private $enrolleeIds;
    private $surveyRoleId;

    /**
     * Create a new job instance.
     *
     * @param $surveyRoleId
     * @param null $color
     */
    public function __construct(array $enrolleeIds, $surveyRoleId, $color = null)
    {
        $this->enrolleeIds  = $enrolleeIds;
        $this->surveyRoleId = $surveyRoleId;
        $this->color        = $color;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count = 0;
        Enrollee::whereIn('id', $this->enrolleeIds)
            ->chunk(100, function ($entries) use (&$count) {
                $newUserIds = collect();
                $entries->each(function ($enrollee) use ($newUserIds, &$count) {
                    $newUserId = (string) Str::uuid();

                    $email = empty($email = $enrollee->email)
                        ? $newUserId.'@careplanmanager.com'
                        : $email;

                    //need this to determine if is_awv. What about if there is no CCDA? e.g. only has eligibility job?
                    $ccda = $enrollee->ccda;
                    $isAwv = $ccda ? Ccda::IMPORTER_AWV === $ccda->source : false;

                    //what about if enrollee already has user?
                    $userCreatedFromEnrollee = (new UserRepository())->createNewUser(
                        new ParameterBag(
                            [
                                'email'        => $email,
                                'password'     => Str::random(30),
                                'display_name' => ucwords(
                                    strtolower($enrollee->first_name.' '.$enrollee->last_name)
                                ),
                                'first_name' => $enrollee->first_name,
                                'last_name'  => $enrollee->last_name,
                                'mrn_number' => $enrollee->mrn,
                                'birth_date' => $enrollee->dob,
                                'username'   => empty($email)
                                    ? $newUserId
                                    : $email,
                                'program_id'        => $enrollee->practice_id,
                                'is_auto_generated' => true,
                                'roles'             => [$this->surveyRoleId],
                                'is_awv'            => $ccda,
                                'address'           => $enrollee->address,
                                'address2'          => $enrollee->address_2,
                                'city'              => $enrollee->city,
                                'state'             => $enrollee->state,
                                'zip'               => $enrollee->zip,

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
                    $this->attachPhones($userCreatedFromEnrollee, $enrollee);

                    //is this going to be a problem if it stays on during the importing phase?
                    $userCreatedFromEnrollee->setBillingProviderId($enrollee->provider->id);

                    $enrollee->update(['user_id' => $userCreatedFromEnrollee->id]);

                    $this->updateEnrolleeSurveyStatuses(
                        $enrollee->id,
                        $userCreatedFromEnrollee->id,
                        null,
                        false,
                        optional($userCreatedFromEnrollee->patientInfo)->id
                    );

                    $newUserIds->push($userCreatedFromEnrollee->id);
                });
                $count += $newUserIds->count();
                event(new AutoEnrollableCollected($newUserIds->toArray(), false, $this->color));
            });

        $target = sizeof($this->enrolleeIds);
        if ($target !== $count) {
            Log::critical("CreateUsersFromEnrollees: Was supposed to create $target, but only created $count.");
        }
    }

    private function attachPhones(User $userCreatedFromEnrollee, Enrollee $enrollee)
    {
        //Self Enrollment uses $user->getPhone() - If there are many Primary Phone Numbers, it will get the one created first
        //1st prio - cell phone
        $cellPhone = $enrollee->cell_phone_e164;
        if ( ! empty($cellPhone)) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::MOBILE,
                'number'     => $cellPhone,
                'is_primary' => true,
            ]);
        }

        //2nd prio - primary phone - will likely be empty
        $primaryPhone = $enrollee->primary_phone_e164;
        if ($primaryPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $primaryPhone,
                'is_primary' => true,
            ]);
        }

        //3rd prio - home phone
        $homePhone = $enrollee->home_phone_e164;
        if ($homePhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'type'       => PhoneNumber::HOME,
                'number'     => $homePhone,
                'is_primary' => true,
            ]);
        }

        //last
        $otherPhone = $enrollee->other_phone_e164;
        if ($otherPhone) {
            $userCreatedFromEnrollee->phoneNumbers()->create([
                'number'     => $otherPhone,
                'is_primary' => false,
            ]);
        }
    }
}
