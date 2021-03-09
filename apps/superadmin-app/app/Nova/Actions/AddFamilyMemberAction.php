<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Nova\PatientUser;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Sloveniangooner\SearchableSelect\SearchableSelect;

class AddFamilyMemberAction extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public $name = 'Add Family Member';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            SearchableSelect::make('Patient', 'patient_id')->resource(PatientUser::class),
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
            throw new \Exception('can only add member to one family');
        }

        $patientId = $fields->get('patient_id');
        $familyId  = $models->first()->id;
        Patient::where('user_id', '=', $patientId)
            ->update(['family_id' => $familyId]);
    }
}
