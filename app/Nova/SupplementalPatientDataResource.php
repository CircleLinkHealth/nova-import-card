<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Importers\SupplementalPatientDataImporter;
use CircleLinkHealth\ClhImportCardExtended\ClhImportCardExtended;
use CircleLinkHealth\Eligibility\Entities\SupplementalPatientData;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;

class SupplementalPatientDataResource extends Resource
{
    public static $group = \App\Constants::NOVA_GROUP_ENROLLMENT;

    public static $importer = SupplementalPatientDataImporter::class;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SupplementalPatientData::class;

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
        return [];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            ClhImportCardExtended::forUser(auth()->user(), self::class),
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
