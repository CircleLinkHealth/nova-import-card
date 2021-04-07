<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ModifyPatientActivityAction;
use App\Nova\Filters\ActivityChargeableServiceFilter;
use App\Nova\Helpers\Utils;
use CircleLinkHealth\SharedModels\Entities\Activity;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Suenerds\NovaSearchableBelongsToFilter\NovaSearchableBelongsToFilter;
use Titasgailius\SearchRelations\SearchesRelations;

class PatientActivity extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_CARE_COACHES;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Activity::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'patient_id',
        'provider_id',
    ];

    public static $searchRelations = [
        'provider' => ['display_name'],
        'patient'  => ['display_name'],
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
        return [
            new ModifyPatientActivityAction(),
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

            BelongsTo::make('Logger', 'provider', User::class)
                ->sortable()
                ->readonly(true),

            Text::make('Patient', 'patient.display_name')
                ->readonly(true),

            Text::make('Type', 'type')
                ->readonly(true),

            Number::make('Duration (s)', 'duration')
                ->readonly(true),

            Text::make('Chargeable Service', function ($row) {
                $styleHack = Utils::getCssToHideEditButton($this);

                /** @var \CircleLinkHealth\Customer\Entities\ChargeableService $cs */
                $cs = \CircleLinkHealth\Customer\Entities\ChargeableService::cached()->firstWhere('id', '=', $row->chargeable_service_id);
                $str = ($cs)->display_name ?? 'N/A';

                return $styleHack."<span>$str</span>";
            })->readonly(true)->asHtml(),
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
            (new NovaSearchableBelongsToFilter('Logger'))
                ->fieldAttribute('provider')
                ->filterBy('provider_id'),
            new ActivityChargeableServiceFilter(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)
            ->where('performed_at', '>=', now()->startOfMonth());
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
