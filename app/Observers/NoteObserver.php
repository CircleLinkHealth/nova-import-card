<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use App\Constants;
use App\Note;
use App\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Entities\PatientNurse;

class NoteObserver
{
    public function created(Note $note)
    {
        if ($note->author->hasRole(Constants::PRACTICE_STAFF_ROLE_NAMES) && $nurse = PatientNurse::getPermanentNurse($note->patient->id)) {
            $nurse->notify(new PracticeStaffCreatedNote($note));
        }
    }
}
