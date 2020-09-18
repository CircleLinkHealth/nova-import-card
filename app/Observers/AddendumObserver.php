<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Models\Addendum;
use App\Note;
use App\Notifications\AddendumCreated;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\Notification;

class AddendumObserver
{
    /**
     * @param $addendum
     * @param $patientId
     * @param $noteAuthorUser
     */
    public function createActivityTaskForAddendum($addendum, $patientId, $noteAuthorUser)
    {
        Call::create([
            'type'            => 'addendum',
            'sub_type'        => 'addendum_response',
            'note_id'         => $addendum->addendumable_id,
            'service'         => 'phone', //all records in the DB have phone.
            'status'          => 'scheduled',
            'asap'            => true,
            'scheduled_date'  => Carbon::parse(now())->toDateString(),
            'inbound_cpm_id'  => $patientId,
            'outbound_cpm_id' => $noteAuthorUser->id,
            'is_cpm_outbound' => 1,
            'scheduler'       => $addendum->author_user_id,
        ]);
    }

    /**
     * Handle the addendum "created" event.
     */
    public function created(Addendum $addendum)
    {
        $addendum->loadMissing('addendumable');
        $addendumable = $addendum->addendumable;
        $patientId    = optional($addendumable)->patient_id;

        if (is_a($addendumable, Note::class)) {
            $noteAuthorUser = $addendumable->author;

            if (is_a($noteAuthorUser, User::class) && auth()->id() !== optional($noteAuthorUser)->id) {
                Notification::send($noteAuthorUser, new AddendumCreated($addendum, auth()->user()));
                $this->createActivityTaskForAddendum($addendum, $patientId, $noteAuthorUser);
            }
        }
    }
}
