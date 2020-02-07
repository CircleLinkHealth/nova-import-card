<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class CpmProblem extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\SharedModels\Entities\CpmProblem::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'default_icd_10_code',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
            ID::make()->sortable(),
            Text::make('Name')->sortable(),
            Text::make('Default ICD-10 Code', 'default_icd_10_code')->sortable()->help(
                'The default code will be shown on the billing report in case we did not receive an ICD-10 code in the CCD.'
            ),
            Text::make('Unique Keywords', 'contains')->sortable()->help('In case conditions in the CCD do not contain any ICD-9, ICD-10, or SNOMED Codes, the importer will use these keywords to see if any of them appear in the problems section of the CCD. The same applies for cases where the CCD contains codes, but CPM could not recognize them. These need to be UNIQUE.'),
            Number::make('Priority', 'weight')->sortable()->help('This helps CPM decide which conditions to include on the billing report in case the patient has multiple conditions. Higher value means higher priority.'),
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
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
}
