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
use Illuminate\Support\Facades\Log;

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
                    /** @var User $userCreatedFromEnrollee */
                    $userCreatedFromEnrollee = $enrollee->user()->updateOrCreate(
                        [
                            'email' => $enrollee->email,
                        ],
                        [
                            'program_id'      => $enrollee->practice_id,
                            'display_name'    => $enrollee->first_name.' '.$enrollee->last_name,
                            'user_registered' => Carbon::parse(now())->toDateTimeString(),
                            'first_name'      => $enrollee->first_name,
                            'last_name'       => $enrollee->last_name,
                            'address'         => $enrollee->address,
                            'address_2'       => $enrollee->address_2,
                            'city'            => $enrollee->city,
                            'state'           => $enrollee->state,
                            'zip'             => $enrollee->zip,
                        ]
                    );

                    $userCreatedFromEnrollee->attachGlobalRole($this->surveyRoleId);

                    $userCreatedFromEnrollee->phoneNumbers()->create([
                        'number'     => $enrollee->primary_phone,
                        'is_primary' => true,
                    ]);

                    $userCreatedFromEnrollee->patientInfo()->create([
                        'birth_date' => $enrollee->dob,
                    ]);

                    // Why this does not work in create query above?
                    $userCreatedFromEnrollee->patientInfo->update([
                        'ccm_status' => Patient::UNREACHABLE,
                    ]);

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
}
