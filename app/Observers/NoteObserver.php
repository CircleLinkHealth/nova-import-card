<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Constants;
use App\Note;
use App\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Entities\PatientNurse;
use CircleLinkHealth\Customer\Entities\User;

class NoteObserver
{
    public function created(Note $note)
    {
        if (User::hasRole(Constants::PRACTICE_STAFF_ROLE_NAMES)->where('id', $note->author_id)->exists() && $nurse = PatientNurse::getPermanentNurse($note->patient_id)) {
            $nurse->notify(new PracticeStaffCreatedNote($note));
        }
    }
}
