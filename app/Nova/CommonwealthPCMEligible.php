<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Eligibility\Entities\EligibilityJob;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CommonwealthPCMEligible extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = EligibilityJob::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'data->referring_provider_name',
        'data->last_encounter',
        'data->primary_insurance',
        'data->secondary_insurance',
        'data->tertiary_insurance',
        'data->primary_phone',
        'data->other_phone',
        'data->home_phone',
        'data->cell_phone',
        'data->email',
        'data->dob',
        'data->first_name',
        'data->last_name',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['targetPatient'];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * Get the cards available for the request.
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
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Provider', 'data->referring_provider_name')->sortable(),
            Text::make('Last Encounter', 'data->last_encounter')->sortable(),
            Text::make('Primary Insurance', 'data->primary_insurance')->sortable(),
            Text::make('Secondary Insurance', 'data->secondary_insurance')->sortable(),
            Text::make('Tertiary Insurance', 'data->tertiary_insurance')->sortable(),
            Text::make('Primary Phone', 'data->primary_phone'),
            Text::make('Other Phone', 'data->other_phone'),
            Text::make('Home Phone', 'data->home_phone'),
            Text::make('Cell Phone', 'data->cell_phone'),
            Text::make('Email', 'data->email'),
            Text::make('Dob', 'data->dob')->sortable(),
            Text::make('Lang', 'data->language'),
            Text::make('Mrn', 'data->mrn_number'),
            Text::make('First Name', 'data->first_name')->sortable(),
            Text::make('Last Name', 'data->last_name')->sortable(),
            Text::make('Address', 'data->street'),
            Text::make('Address 2', 'data->street2'),
            Text::make('City', 'data->city'),
            Text::make('State', 'data->state'),
            Text::make('Zip', 'data->zip'),
            Text::make('Medical Record Type', 'data->medical_record_type'),
            Text::make('Medical Record Id', 'data->medical_record_id'),
            ID::make()->sortable(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->whereJsonLength(
            'data->chargeable_services_codes_and_problems->G2065',
            '>',
            0
        );
    }

    public static function label()
    {
        return 'G2065 Eligible';
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->whereJsonLength(
            'data->chargeable_services_codes_and_problems->G2065',
            '>',
            0
        );
    }
}
