<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Filters\DailyDisputesStatus;
use CircleLinkHealth\SharedModels\Entities\NurseInvoiceDailyDispute;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class InvoiceDailyDisputesApproval extends Resource
{
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
    public static $model = NurseInvoiceDailyDispute::class;

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

            BelongsTo::make('Care Coach', 'nurseInvoice', NurseInvoice::class)
                ->hideFromIndex()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Text::make('Care Coach', 'nurseInvoice.invoice_data.nurseFullName')
                ->readonly(true)
                ->sortable(),

            Date::make('Date', 'disputed_day')
                ->readonly(true)
                ->sortable(),

            Text::make('Time disputed', 'disputed_formatted_time')
                ->readonly(true)
                ->sortable(),

            Text::make('Time Suggested', 'suggested_formatted_time')
                ->readonly(true)
                ->sortable(),

            Select::make('Approve / Reject', 'status')
                ->options(
                    [
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ]
                )->hideFromIndex()
                ->rules('required'),

            Text::make('Status', 'status')
                ->hideWhenUpdating()
                ->sortable(),
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
            new DailyDisputesStatus(),
        ];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Invoice Daily Disputes';
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
