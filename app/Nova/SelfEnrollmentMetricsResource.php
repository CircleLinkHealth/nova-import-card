<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Constants;
use App\SelfEnrollmentMetricsEnrollee;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;

class SelfEnrollmentMetricsResource extends Resource
{
    public static $group = Constants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SelfEnrollmentMetricsEnrollee::class;

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
    public static $title = 'batch_id';

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
            Text::make('Batch Date', 'batch_date')->sortable(),
            Text::make('Batch Time', 'batch_time')->sortable(),
            Text::make('Practice Name', 'practice_name')->sortable(),
            Text::make('Button Color', 'button_color')->sortable(),
            Text::make('Total Invites Sent', 'total_invites_sent')->sortable(),
            Text::make('Total Invites Opened', 'total_invites_opened')->sortable(),
            Text::make('% Invites Opened', 'percentage_invites_opened')->sortable(),
            Text::make('Total Seen Letter', 'total_saw_letter')->sortable(),
            Text::make('% Seen Letter', 'percentage_saw_letter')->sortable(),
            Text::make('Total Seen Form', 'total_saw_form')->sortable(),
            Text::make('% Seen Form', 'percentage_saw_form')->sortable(),
            Text::make('Total Enrolled', 'total_enrolled')->sortable(),
            Text::make('% Enrolled', 'percentage_enrolled')->sortable(),
            Text::make('Total Call Requests', 'total_call_requests')->sortable(),
            Text::make('% Call Requests', 'percentage_call_requests')->sortable(),
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
     * @return string
     */
    public static function label()
    {
        return 'Invitations Panel';
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
