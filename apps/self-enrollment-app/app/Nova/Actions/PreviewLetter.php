<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class PreviewLetter extends Action
{
    use InteractsWithQueue;
    use Queueable;

    private ?int $practiceId;

    /**
     * PreviewLetter constructor.
     */
    public function __construct(?int $practiceId)
    {
        $this->practiceId = $practiceId;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $enrollees   = $this->enrolleesForDropdown();
        $placeholder = 'Choose patient to review letter for';

        if ($enrollees->isEmpty()) {
            $placeholder = 'No patients marked for self enrollment. Use my user as patient avatar';
        }

        return [
            Select::make('Enrollee to review letter for')
                ->options($enrollees)
                ->withMeta([
                    'placeholder' => $placeholder,
                ]),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $count      = $models->count();
        $authId     = auth()->id();
        $practiceId = $models->first()->practice_id;

        if ( ! $authId || ! $practiceId) {
            return Action::message('Error! Authenticated id or practice id not found.');
        }

        if ($count > 1) {
            return Action::message('Forbidden! Should not execute action for more than one letter.');
        }

        $idForUrl = $authId;

        if ($fields->enrollee_to_review_letter_for) {
            $idForUrl = $fields->enrollee_to_review_letter_for;
        }

        return Action::openInNewTab(url("self-enrollment-review/$practiceId/$idForUrl"));
    }

    private function enrolleesForDropdown(): Collection
    {
        if ( ! $this->practiceId) {
            return collect();
        }

        return User::ofPractice($this->practiceId)
            ->ofType('survey-only')
            ->whereHas('enrollee', function ($q) {
                // @var Enrollee $q
                $q->canSendSelfEnrollmentInvitation(true);
            })
            ->uniquePatients()
            ->pluck('display_name', 'id');
    }
}
