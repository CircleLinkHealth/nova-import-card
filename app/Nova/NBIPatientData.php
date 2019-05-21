<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Models\PatientData\NBI\PatientData;
use App\Nova\Importers\NBIPatientData as NBIPatientDataImporter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Sparclex\NovaImportCard\NovaImportCard;

class NBIPatientData extends Resource
{
    public static $importer = NBIPatientDataImporter::class;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PatientData::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'dob',
        'first_name',
        'last_name',
        'mrn',
        'primary_insurance',
        'provider',
        'secondary_insurance',
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
            Text::make('first_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('required', 'string'),
            Text::make('last_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('required', 'string'),
            Number::make('mrn')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('required', 'integer'),
            Date::make('dob')
                ->sortable()
                ->format('MM/DD/YYYY')->creationRules('required', 'date')
                ->updateRules('required', 'date'),
            Text::make('provider')
                ->sortable(),
            Text::make('primary_insurance')
                ->sortable(),
            Text::make('secondary_insurance')
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
