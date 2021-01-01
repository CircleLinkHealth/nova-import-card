<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Observers;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Notifications\PracticeStaffCreatedNote;
use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\SharedModels\Entities\Note;

class NoteObserver
{
    public function created(Note $note)
    {
        if (User::ofType(CpmConstants::PRACTICE_STAFF_ROLE_NAMES)->where('id', $note->author_id)->exists() && $nurse = app(NurseFinderEloquentRepository::class)->assignedNurse($note->patient_id)) {
            optional($nurse->permanentNurse)->notify(new PracticeStaffCreatedNote($note));
        }
    }
}
