<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\Entities\PatientNurse;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class NursePatient extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \App\Constants::NOVA_GROUP_CARE_COACHES;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PatientNurse::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'patient_user_id',
        'nurse_user_id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'patient' => ['id', 'display_name', 'first_name', 'last_name'],
        'nurse'   => ['id', 'display_name', 'first_name', 'last_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()
                ->hideFromDetail()
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->hideFromIndex(),

            Text::make('Patient Id', 'patient.id')
                ->sortable()
                ->hideWhenCreating()
                ->readonly(true),

            Text::make('Patient', 'patient.display_name')
                ->hideFromIndex()
                ->hideFromDetail()
                ->hideWhenCreating()
                ->readonly(true),

            BelongsTo::make('Patient', 'patient', User::class)
                ->sortable()
                ->searchable()
                ->hideWhenUpdating()
                ->readonly(true),

            BelongsTo::make('Nurse', 'nurse', User::class)
                ->searchable()
                ->sortable(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    public static function label()
    {
        return 'Nurse - Patient Assignment';
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
}
