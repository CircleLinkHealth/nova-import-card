<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class RemoveFamilyMemberAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Remove Family Member';

    private array $familyMembers;

    /**
     * RemoveFamilyMemberAction constructor.
     * @param mixed $familyMembers
     */
    public function __construct($familyMembers)
    {
        $this->familyMembers = $familyMembers;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Patient', 'patient_id')->options($this->familyMembers),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() > 1) {
            throw new \Exception('can only remove member from one family');
        }

        $patientId = $fields->get('patient_id');
        Patient::where('user_id', '=', $patientId)
            ->update(['family_id' => null]);
    }
}
