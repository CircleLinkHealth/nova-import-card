<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientForcedChargeableService as PatientForcedChargeableServiceModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class PatientForcedChargeableService extends Resource
{
    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PatientForcedChargeableServiceModel::class;

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
    public static $title = 'id';

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

            BelongsTo::make('Chargeable Service', 'chargeableService', ChargeableService::class),

            BelongsTo::make('Patient', 'patient', ManagePatientForcedChargeableServices::class),

            Select::make('Action Type', 'action_type')->options([
                PatientForcedChargeableServiceModel::FORCE_ACTION_TYPE => 'Force Service',
                PatientForcedChargeableServiceModel::BLOCK_ACTION_TYPE => 'Block Service',
            ]),
            Text::make('For Month')->displayUsing(function () {
                return isset($this->chargeable_month) && ! is_null($this->chargeable_month)
                    ? Carbon::parse($this->chargeable_month)->toDateString()
                    : 'Permanently';
            })->readonly()->onlyOnIndex(),
            Select::make('Chargeable Month', 'chargeable_month')->options([
                null                                                      => 'Permanently',
                Carbon::now()->startOfMonth()->toDateString()             => 'Current month only',
                Carbon::now()->subMonth()->startOfMonth()->toDateString() => 'Past month only',
            ]),
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
    public static function relatableChargeableServices(NovaRequest $request, $query)
    {
        return $query->whereNotNull('display_name');
    }
}
