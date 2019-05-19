<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Enrollee;
use App\Nova\Importers\EnroleeData as EnroleeDataImporter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Sparclex\NovaImportCard\NovaImportCard;

class EnroleeData extends Resource
{
    public static $importer = EnroleeDataImporter::class;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Enrollee::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'practice_id',
        'mrn',
        'first_name',
        'last_name',
        'address',
        'address_2',
        'city',
        'state',
        'zip',
        'primary_phone',
        'dob',
        'primary_insurance',
        'secondary_insurance',
        'tertiary_insurance',
        'email',
        'referring_provider_name',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'mrn';

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
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            new NovaImportCard(self::class),
        ];
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
            ID::make()->sortable(),

            BelongsTo::make('Provider', 'provider', User::class)
                ->sortable(),

            Text::make('first_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('last_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('address')
                ->sortable()
                ->creationRules('string')
                ->updateRules('string'),

            Number::make('mrn')
                ->sortable()
                ->creationRules('integer')
                ->updateRules('integer'),

            Date::make('dob')
                ->sortable()
                ->format('MM/DD/YYYY')->creationRules('date')
                ->updateRules('date'),

            Text::make('primary_insurance')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('secondary_insurance')
                ->sortable()
                ->creationRules('string')
                ->updateRules('string'),
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
