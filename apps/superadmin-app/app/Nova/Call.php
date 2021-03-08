<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Lenses\PamEndOfMonthReport;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Call extends Resource
{
    public static $group = CpmConstants::NOVA_GROUP_CARE_COACHES;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\SharedModels\Entities\Call::class;

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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Type', function (\CircleLinkHealth\SharedModels\Entities\Call $row) {
                if ($row->sub_type) {
                    return $row->sub_type;
                }

                return $row->type ?? 'Call';
            }),
            BelongsTo::make('Note', 'note', Note::class),
            Text::make('status'),
            BelongsTo::make('Patient', 'inboundUser', PatientUser::class),
            BelongsTo::make('Nurse', 'outboundUser', User::class),
            Text::make('Scheduled Date', 'scheduled_date'),
            Text::make('Called Date', 'called_date'),
            Text::make('Scheduler', 'scheduler'),
            Boolean::make('Is Manual', 'is_manual'),
            Boolean::make('ASAP', 'asap'),
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
        return [new PamEndOfMonthReport()];
    }
}
