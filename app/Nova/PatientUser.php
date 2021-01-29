<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ModifyPatientTimeAction;
use App\Nova\Actions\UserEnroll;
use App\Nova\Actions\UserUnreachable;
use App\Nova\Actions\UserWithdraw;
use App\Nova\User as NovaUser;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class PatientUser extends NovaUser
{
    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = true;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = CpmConstants::NOVA_GROUP_ADMIN;

    public function actions(Request $request)
    {
        return [
            new UserUnreachable(),
            new UserEnroll(),
            new UserWithdraw(),
            (new ModifyPatientTimeAction())
                ->confirmText("Modifying the duration may have side-effects on patient's time and care coach's compensation. Are you sure you want to proceed?")
                ->confirmButtonText('Done')
                ->cancelButtonText('Cancel')
                ->onlyOnDetail(true)
        ];
    }

    public function fields(Request $request)
    {
        $fields = parent::fields($request);

        return array_merge($fields, [
            Text::make('CCM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getCcmTime());
            })
                ->sortable()
                ->onlyOnDetail()
                ->readonly(true),

            Text::make('CCM (RHC/FQHC)', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getRhcTime());
            })
                ->sortable()
                ->onlyOnDetail()
                ->readonly(true),

            Text::make('BHI', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getBhiTime());
            })
                ->sortable()
                ->onlyOnDetail()
                ->readonly(true),

            Text::make('RPM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getRpmTime());
            })
                ->sortable()
                ->onlyOnDetail()
                ->readonly(true),

            Text::make('PCM', function (\CircleLinkHealth\Customer\Entities\User $row) {
                return $row->formattedTime($row->getPcmTime());
            })
                ->sortable()
                ->onlyOnDetail()
                ->readonly(true),
        ]);
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

    public static function label()
    {
        return 'Patients';
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
