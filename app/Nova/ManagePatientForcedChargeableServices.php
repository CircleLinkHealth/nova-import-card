<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Filters\UserPracticeFilter;
use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientServicesForTimeTracker;
use CircleLinkHealth\Customer\Entities\User as CpmUser;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ManagePatientForcedChargeableServices extends Resource
{
    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = true;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CpmUser::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'display_name',
        'email',
        'first_name',
        'last_name',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'primaryPractice' => ['display_name'],
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
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
        ];
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

    public static function availableForNavigation(Request $request)
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the cards available for the request.
     *
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
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make('Display Name', 'display_name')->sortable(),

            BelongsToMany::make('Forced Chargeable Services', 'forcedChargeableServices', 'App\Nova\ChargeableService')
                ->fields(function () {
                    return [
                        Boolean::make('Is forced', 'is_forced')->displayUsing(function () {
                            return $this->pivot->is_forced ?? '-';
                        }),
                        Text::make('For Month', 'chargeable_month')->displayUsing(function () {
                            return isset($this->pivot->chargeable_month) && ! is_null($this->pivot->chargeable_month)
                                ? Carbon::parse($this->pivot->chargeable_month)->toDateString()
                                : '-';
                        })->readonly()->onlyOnIndex(),
                        Select::make('Chargeable Month', 'chargeable_month')->options([
                            null                                                      => 'Permanently',
                            Carbon::now()->startOfMonth()->toDateString()             => 'Current month only',
                            Carbon::now()->subMonth()->startOfMonth()->toDateString() => 'Past month only',
                        ]),
                    ];
                }),

            Text::make('Patient Eligible Chargeable Services', function () {
                $summaries = (new PatientServicesForTimeTracker($this->id, Carbon::now()->startOfMonth()))
                    ->get()
                    ->map(function ($s) {
                        $minutes = secondsToMMSS($s->total_time);

                        return "<li>$s->chargeable_service_name - $s->chargeable_service_code (time: $minutes)</li>";
                    })->implode('');

                return "<ul>$summaries</ul>";
            })->onlyOnDetail()
                ->readonly()
                ->asHtml(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new UserPracticeFilter(),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->ofType('participant');
    }

    /**
     * Get the lenses available for the resource.
     *
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
        return $query->ofType('participant');
    }
}
