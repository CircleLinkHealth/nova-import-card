<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\Nova\Filters\BillableTimeFilter;
use App\Nova\Filters\PageTimerDurationFilter;
use App\Nova\Filters\TimestampFilter;
use Carbon\Carbon;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Suenerds\NovaSearchableBelongsToFilter\NovaSearchableBelongsToFilter;
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
        'id',
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
        'logger'  => ['display_name', 'first_name', 'last_name'],
        'patient' => ['display_name', 'first_name', 'last_name'],
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
        /** @var NovaRequest $req */
        $req    = $request;
        $fields = $req->isResourceDetailRequest() ? $this->getFieldsForActivities($this->resource) : [];

        return array_merge([
            ID::make()->sortable(),

            BelongsTo::make('Logger', 'logger', User::class)
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

            Boolean::make('Billable', function ($row) {
                return $row->activities->isNotEmpty();
            }),

            Text::make('Service(s)', function ($row) {
                return implode(', ', $row->activities->map(fn (Activity $a) => $a->chargeableService->code)->toArray());
            }),
        ], $fields);
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new BillableTimeFilter(),
            new PageTimerDurationFilter(),
            (new NovaSearchableBelongsToFilter())
                ->fieldAttribute('logger')
                ->filterBy('provider_id'),
            new TimestampFilter('From', 'start_time', 'from', Carbon::now()->startOfMonth()),
            new TimestampFilter('To', 'start_time', 'to', Carbon::now()->endOfMonth()),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->with([
            'logger' => function ($q) {
                $q->without(['roles', 'perms'])
                    ->select(['id', 'display_name']);
            },
            'patient' => function ($q) {
                $q->without(['roles', 'perms'])
                    ->select(['id', 'display_name']);
            },
            'activities' => function ($q) {
                $q->with([
                    'chargeableService' => fn ($q) => $q->select(['id', 'code']),
                ])
                    ->select(['id', 'page_timer_id', 'chargeable_service_id']);
            },
        ]);
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

    private function getFieldsForActivities(PageTimer $pageTimer)
    {
        $fields = [];

        /** @var \CircleLinkHealth\Customer\Entities\ChargeableService[]|Collection $chargeableServices */
        $chargeableServices = \CircleLinkHealth\Customer\Entities\ChargeableService::whereIn('id', $pageTimer->activities
            ->pluck('chargeable_service_id'))
            ->get();

        $len                = $pageTimer->activities->count();
        $modifiableCsCode   = null;
        $modifiableDuration = null;
        for ($i = 0; $i < $len; ++$i) {
            $activity = $pageTimer->activities[$i];
            $csCode   = $chargeableServices->firstWhere('id', '=', $activity->chargeable_service_id)->code;
            $fields[] = Text::make("Activity for $csCode", function () use ($activity) {
                return $activity->duration;
            });
            if ($i === ($len - 1)) {
                $modifiableCsCode   = $csCode;
                $modifiableDuration = $activity->duration;
            }
        }

        if (sizeof($fields) > 1) {
            $fields[] = Text::make('NOTE', function () use ($modifiableCsCode, $modifiableDuration) {
                return "This time tracker entry has activities for multiple chargeable services. You can only modify duration of $modifiableCsCode. Maximum $modifiableDuration seconds.";
            });
        }

        return $fields;
    }
}
