<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService as Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChargeableService extends Resource
{
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
        'id',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'display_name';

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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('code'),

            BelongsToMany::make('Patients', 'forcedForPatients', 'App\Nova\ManagePatientForcedChargeableServices')->fields(function () {
                return [
                    Boolean::make('Is forced', 'is_forced')->displayUsing(function () {
                        return $this->pivot->is_forced ?? '-';
                    }),
                    Text::make('For Month', 'chargeable_month')->displayUsing(function () {
                        return isset($this->pivot->chargeable_month) && ! is_null($this->pivot->chargeable_month)
                            ? Carbon::parse($this->pivot->chargeable_month)->toDateString()
                            : '-';
                    })->readonly()->onlyOnDetail(),
                    Select::make('Chargeable Month', 'chargeable_month')->options([
                        null                                                      => 'Permanently',
                        Carbon::now()->startOfMonth()->toDateString()             => 'Current month only',
                        Carbon::now()->subMonth()->startOfMonth()->toDateString() => 'Past month only',
                    ]),
                ];
            })->hideFromIndex()
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
        return $query->whereNotNull('display_name');
    }
}
