<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class Nurse extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \App\Constants::NOVA_GROUP_CARE_COACHES;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Customer\Entities\Nurse::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'user_id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'user' => ['display_name', 'first_name', 'last_name'],
    ];
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @return
     */
    public static $title = 'user_id';

    public static $with = ['user'];

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
        return true;
    }

    public function authorizedToView(Request $request)
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
            BelongsTo::make('Care Coach', 'user', CareCoachUser::class)
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->readonly(),

            Date::make('Start Date', 'start_date')
                ->readonly(function () {
                    // user has not logged in into the system yet
                    // start date can be edited, so readOnly should be false
                    if (is_null($this->resource->user->last_session_id)) {
                        return false;
                    }

                    /** @var Carbon $startDate */
                    $startDate = $this->resource->start_date;
                    if ( ! $startDate) {
                        //user has logged in at least once, so we cannot change start date
                        return true;
                    }

                    // start date is in the future. so readOnly should be false
                    $isTodayOrInTheFuture = $startDate->greaterThanOrEqualTo(now()->startOfDay());

                    return ! $isTodayOrInTheFuture;
                }),

            Text::make('+ Days Payment', 'pay_interval')
                ->rules('required'),

            Number::make('Case Load Capacity', 'case_load_capacity'),

            Boolean::make('Is Active?', 'status')
                ->trueValue('active')
                ->falseValue('inactive'),

            Boolean::make('Is Demo?', 'is_demo'),

            //CPM-2131
            Boolean::make('Fixed VF or Fixed Hourly', 'is_variable_rate'),

            Number::make('Hourly Rate (fixed rate)', 'hourly_rate')
                ->step(0.01),

            Number::make('Visit Fee 1', 'visit_fee')
                ->step(0.01),
            Number::make('Visit Fee 2', 'visit_fee_2')
                ->step(0.01),
            Number::make('Visit Fee 3', 'visit_fee_3')
                ->step(0.01),

            Number::make('High Rate 1', 'high_rate')
                ->step(0.01),
            Number::make('High Rate 2', 'high_rate_2')
                ->step(0.01),
            Number::make('High Rate 3', 'high_rate_3')
                ->step(0.01),
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
        return 'Pay Details';
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
        ];
    }
}
