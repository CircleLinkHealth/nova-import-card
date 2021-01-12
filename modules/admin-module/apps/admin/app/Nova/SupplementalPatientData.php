<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ImportSupplementalPatientData;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData as Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;

class SupplementalPatientData extends Resource
{
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_ENROLLMENT;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Model::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'practice_id',
        'dob',
        'first_name',
        'last_name',
        'mrn',
        'primary_insurance',
        'provider',
        'location',
        'secondary_insurance',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'mrn';

    public static $with = ['practice'];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new ImportSupplementalPatientData(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('Practice', 'practice')
                ->sortable()
                ->rules('required'),
            Text::make('first_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('required', 'string'),
            Text::make('last_name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('required', 'string'),
            Text::make('mrn')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('required', 'integer'),
            Date::make('dob')
                ->sortable()
                ->rules('required'),
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
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Supplemental Data';
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
