<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use App\Nova\Actions\DownloadCsv;
use App\SelfEnrollmentMetrics;
use Circlelinkhealth\EnrollmentInvites\EnrollmentInvites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Fields\Text;

class SelfEnrollmentMetricsResource extends Resource
{
    const AUTO_ENROLLMENT_INVITATIONS_PANEL = '/superadmin/resources/auto-enrollment-invitation-panels';
    public static $group                    = CpmConstants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SelfEnrollmentMetrics::class;

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
        return [
            (new DownloadCsv())->setOnlyColumns([
                'batch_date',
                'batch_time',
                'practice_name',
                'button_color',
                'total_invites_sent',
                'total_invites_opened',
                'percentage_invites_opened',
                'total_seen_letter',
                'percentage_seen_letter',
                'total_seen_form',
                'percentage_seen_form',
                'total_enrolled',
                'percentage_enrolled',
                'total_call_requests',
                'percentage_call_requests',
            ], true)->canSee(function () {
                return true;
            })->canRun(function () {
                return true;
            }),
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
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new EnrollmentInvites())->withMeta(
                [
                    'use_redirect_button' => true,
                    'redirect_url'        => URL::to(self::AUTO_ENROLLMENT_INVITATIONS_PANEL),
                ]
            ),
        ];
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
            Text::make('Total Seen Letter', 'total_seen_letter')->sortable(),
            Text::make('% Seen Letter', 'percentage_seen_letter')->sortable(),
            Text::make('Total Seen Form', 'total_seen_form')->sortable(),
            Text::make('% Seen Form', 'percentage_seen_form')->sortable(),
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
        return 'Invitation Dashboard';
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
