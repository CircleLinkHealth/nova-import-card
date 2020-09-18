<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\Note;
use App\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Entities\User;

class NoteObserver
{
    public function created(Note $note)
    {
        if (User::ofType(CpmConstants::PRACTICE_STAFF_ROLE_NAMES)->where('id', $note->author_id)->exists() && $nurse = app(NurseFinderEloquentRepository::class)->assignedNurse($note->patient_id)) {
            $nurse->notify(new PracticeStaffCreatedNote($note));
        }
    }
}
