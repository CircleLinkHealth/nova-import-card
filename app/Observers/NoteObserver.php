<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Observers;

use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use App\Constants;
use CircleLinkHealth\SharedModels\Entities\Note;
use App\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Entities\User;

class NoteObserver
{
    public function created(Note $note)
    {
        if (User::ofType(Constants::PRACTICE_STAFF_ROLE_NAMES)->where('id', $note->author_id)->exists() && $nurse = app(NurseFinderEloquentRepository::class)->assignedNurse($note->patient_id)) {
            optional($nurse->permanentNurse)->notify(new PracticeStaffCreatedNote($note));
        }
    }
}
