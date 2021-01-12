<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\MorphedByMany;
use Laravel\Nova\Fields\Text;

class CpmInstructable extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\SharedModels\Entities\CpmInstructable::class;
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

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

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
            MorphedByMany::make('CpmProblem')
                ->hideWhenUpdating()
                ->hideFromDetail(),

            BelongsTo::make('cpmInstruction')
                ->hideFromIndex()
                ->hideWhenUpdating(),

            Text::make('CpmProblem', function ($q) {
                return $q->cpmProblem[0]->name;
            }),

            Text::make('cpmInstruction.name')->displayUsing(function ($q) {
                return substr($q, 0, 50);
            })->hideWhenUpdating()
                ->hideFromDetail(),
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
        return 'Cpm Problem - Instruction';
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
