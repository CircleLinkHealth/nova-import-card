<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ModifyPatientTimeAction;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class PatientTime extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = CpmConstants::NOVA_GROUP_CARE_COACHES;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Customer\Entities\User::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'display_name',
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
            (new ModifyPatientTimeAction())
                ->confirmText("Modifying the duration may have side-effects on patient's time and care coach's compensation. Are you sure you want to proceed?")
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
            ID::make('Patient ID', 'id')->sortable(),

            Text::make('Name', 'display_name')
                ->sortable()
                ->readonly(true),

            Text::make('CCM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getCcmTime());
            })
                ->sortable()
                ->readonly(true),

            Text::make('CCM (RHC/FQHC)', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getRhcTime());
            })
                ->sortable()
                ->readonly(true),

            Text::make('BHI', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getBhiTime());
            })
                ->sortable()
                ->readonly(true),

            Text::make('RPM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getRpmTime());
            })
                ->sortable()
                ->readonly(true),

            Text::make('PCM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getPcmTime());
            })
                ->sortable()
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
        return [];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->ofType('participant');
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
