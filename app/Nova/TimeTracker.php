<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Nova\Filters\NurseFilter;
use App\Nova\Filters\TimestampFilter;
use Carbon\Carbon;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class TimeTracker extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = Constants::NOVA_GROUP_CARE_COACHES;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PageTimer::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'title',
        'url_short',
        'patient_id',
        'provider_id',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'logger.user'  => ['display_name', 'first_name', 'last_name'],
        'patient.user' => ['display_name', 'first_name', 'last_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new Actions\ModifyTimeTracker())
                ->confirmText("Modifying the duration may have side-effects on patient's ccm/bhi time and care coach's compensation. Are you sure you want to proceed?")
                ->confirmButtonText('Done')
                ->cancelButtonText('Cancel')
                ->onlyOnDetail(true),
        ];
    }

    /**
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function authorizedToView(Request $request)
    {
        return auth()->user()->isAdmin();
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

            Text::make('Logger (i.e. Care Coach, Provider, Admin)', 'logger.display_name')
                ->sortable()
                ->readonly(true),

            Text::make('Patient', 'patient.display_name')
                ->sortable()
                ->readonly(true),

            Text::make('Activity', 'activity_type')
                ->sortable()
                ->readonly(true),

            Number::make('Duration (seconds)', 'duration')
                ->sortable()
                ->readonly(true),

            DateTime::make('Date Time', 'start_time')
                ->sortable()
                ->readonly(true),

            Boolean::make(
                'Is CCM',
                function () {
                    /** @var PageTimer $entry */
                    $entry = $this->resource;

                    if ($entry->activity) {
                        return ! $entry->activity->is_behavioral;
                    }

                    return false;
                }
            )
                ->readonly(true),

            Boolean::make(
                'Is BHI',
                function () {
                    /** @var PageTimer $entry */
                    $entry = $this->resource;

                    if ($entry->activity) {
                        return $entry->activity->is_behavioral;
                    }

                    return false;
                }
            )
                ->readonly(true),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new NurseFilter('provider_id'),
            new TimestampFilter('From', 'start_time', 'from', Carbon::now()->startOfMonth()),
            new TimestampFilter('To', 'start_time', 'to', Carbon::now()->endOfMonth()),
        ];
    }

    public static function label()
    {
        return 'Time Tracking';
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
