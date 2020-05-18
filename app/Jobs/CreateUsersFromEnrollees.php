<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Events\AutoEnrollableCollected;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PhoneNumber;
use CircleLinkHealth\Customer\Entities\Role;
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
     * @var Enrollee
     */
    private $enrolleeIds;
    /**
     * @var \Illuminate\Database\Eloquent\Model|Role
     */
    private $surveyRole;

    /**
     * CreateUsersFromEnrollees constructor.
     */
    public function __construct(array $enrolleeIds)
    {
        $this->enrolleeIds = $enrolleeIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count            = 0;
        $this->surveyRole = $this->surveyRole();
        Enrollee::whereIn('id', $this->enrolleeIds)
            ->chunk(100, function ($entries) use (&$count) {
                $newUserIds = collect();
                $entries->each(function ($enrollee) use ($newUserIds, &$count) {
                    // UNREACHABLE_PATIENT marks unreachable patients in enrollee model. Doesnt get updated.
                    if ( ! empty($enrollee->user_id) && Enrollee::UNREACHABLE_PATIENT === $enrollee->source) {
                        Log::warning("Enrollee with id [$enrollee->id] is unreachable patient. Should not be here");

                        return;
                    }

                    // If Enrollee and invited again (has user_id) the dont create user. Just invite
                    if ( ! empty($enrollee->user_id) && Enrollee::QUEUE_AUTO_ENROLLMENT === $enrollee->status) {
                        $newUserIds->push($enrollee->user_id);

                        return;
                    }

                    // If any other reason then something is wrong
                    if ( ! empty($enrollee->user_id)) {
                        Log::critical("Enrollee with id [$enrollee->id] should not have reached this point");

                        return;
                    }

                    $email = self::sanitizeEmail($enrollee);

                    $ccda = $enrollee->ccda;
                    $isAwv = false;
                    if ($ccda) {
                        $ccda->billing_provider_id = $enrollee->provider->id;
                        $isAwv = Ccda::IMPORTER_AWV === $ccda->source;
                    }

                    //what about if enrollee already has user?
                    $userCreatedFromEnrollee = (new UserRepository())->createNewUser(
                        new ParameterBag(
                            [
                                'email'        => $email,
                                'password'     => Str::random(30),
                                'display_name' => ucwords(
                                    strtolower($enrollee->first_name.' '.$enrollee->last_name)
                                ),
                                'first_name'        => $enrollee->first_name,
                                'last_name'         => $enrollee->last_name,
                                'mrn_number'        => $enrollee->mrn,
                                'birth_date'        => $enrollee->dob,
                                'username'          => $email,
                                'program_id'        => $enrollee->practice_id,
                                'is_auto_generated' => true,
                                'roles'             => [$this->surveyRole->id],
                                'is_awv'            => $isAwv,
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

                    if ($ccda) {
                        $ccda->patient_id = $userCreatedFromEnrollee->id;
                        $ccda->save();
                    }

                    //handle phones here, we wanna make sure enrollee cell_phone gets priority, since we are sending SMS.
                    //This is worth investigating. When Zack goes through enrollees to see if they are eligible for auto enrollment, does he check phones?
                    //Do we need to know anything about his validation process, to make sure we get the correct number here?
                    $this->attachPhones($userCreatedFromEnrollee, $enrollee);

                    //is this going to be a problem if it stays on during the importing phase?
                    $userCreatedFromEnrollee->setBillingProviderId($enrollee->provider->id);

                    $enrollee->update(
                        [
                            'user_id' => $userCreatedFromEnrollee->id,
                        ]
                    );

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
//                SHOULD NOT BE IN THIS CLASS. BETTER TO MOVE
//                event(new AutoEnrollableCollected($newUserIds->toArray(), false, $this->color));
            });

        $target = sizeof($this->enrolleeIds);
        if ($target !== $count) {
            Log::critical("CreateUsersFromEnrollees: Was supposed to create $target, but only created $count.");
        }
    }

    public static function sanitizeEmail(Enrollee $enrollee): ?string
    {
        if (empty($enrollee->email) || in_array(strtolower($enrollee->email), ['noemail@noemail.com', 'null'])) {
            return "e{$enrollee->id}@careplanmanager.com";
        }

        return $enrollee->email;
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

    private function surveyRole(): Role
    {
        $this->surveyRole = Role::firstOrCreate(
            [
                'name' => 'survey-only',
            ],
            [
                'display_name' => 'Survey User',
                'description'  => 'Became Users just to be enrolled in AWV survey',
            ]
        );

        return $this->surveyRole;
    }
}
