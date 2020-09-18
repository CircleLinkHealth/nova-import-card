<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use App\Models\PracticePull\Medication;
use App\Nova\Actions\PracticePull\ImportMedications;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class PracticePullMedication extends Resource
{
    public static $group = CpmConstants::NOVA_GROUP_PRACTICE_DATA_PULLS;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Medication::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'mrn',
        'name',
        'sig',
        'start',
        'stop',
        'status',
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
     * @return array
     */
    public function actions(Request $request)
    {
        return [new ImportMedications()];
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
            Text::make('MRN', 'mrn')->sortable(),
            Text::make('Name', 'name')->sortable(),
            Text::make('Sig', 'sig')->sortable(),
            Date::make('Start', 'start')->sortable(),
            Date::make('Stop', 'stop')->sortable(),
            Text::make('Status', 'status')->sortable(),
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

    public static function label()
    {
        return 'Medications';
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
