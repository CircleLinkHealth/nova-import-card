<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\CcdaView;
use CircleLinkHealth\Customer\CpmConstants;
use App\Nova\Actions\DownloadCsv;
use App\Nova\Actions\ImportCcdaAction;
use App\Nova\Actions\ReimportCcda;
use App\Nova\Filters\CcdaView\ImportedCcdaViewFilter;
use App\Nova\Filters\CcdaView\ValidationErrorsCcdaFilter;
use App\Nova\Filters\CpmDateFilter;
use App\Nova\Filters\PracticeFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Ccda extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = CpmConstants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CcdaView::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'ccda_id',
        'patient_user_id',
        'mrn',
        'first_name',
        'last_name',
        'dm_from',
        'dob',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'ccda_id';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ImportCcdaAction())->canSee(function () {
                return true;
            })->canRun(function () {
                return true;
            }),
            (new ReimportCcda())->canSee(function () {
                return true;
            })->canRun(function () {
                return true;
            }),
            (new DownloadCsv())->setOnlyColumns([
                'first_name',
                'last_name',
                'mrn',
                'dob',
                'provider',
                'practice_display_name',
                'patient_user_id',
            ], false)->canSee(function () {
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
            Text::make('CarePlan', function ($row) {
                if ( ! $row->patient_user_id) {
                    return '';
                }

                return link_to_route('patient.careplan.print', 'View', [$row->patient_user_id])->toHtml();
            })->asHtml(),
            Text::make('First Name', 'first_name')->sortable(),
            Text::make('Last Name', 'last_name')->sortable(),
            Date::make('DOB', 'dob')->sortable(),
            Text::make('Mrn', 'mrn')->sortable(),
            Text::make('Provider', 'provider_name')->sortable(),
            Text::make('Practice', 'practice_display_name')->sortable(),
            Text::make('Nurse', 'nurse_user_name')->sortable(),
            Text::make('From (DM)', 'dm_from')->sortable(),
            DateTime::make('Created At', 'created_at')->sortable(),
            Code::make('Errors', 'validation_errors')->json(),
            Text::make('Source', 'source')->sortable(),
            Text::make('patient_user_id')->sortable(),
            ID::make('ccda_id')->sortable(),
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
            new PracticeFilter(),
            (new CpmDateFilter('created_at'))->setName('Created on or after')->setOperator('>=')->setDefaultDate(now()->subWeeks(2)->toDateTimeString()),
            (new CpmDateFilter('created_at'))->setName('Created before or on')->setOperator('<=')->setDefaultDate(now()->addDay()->toDateTimeString()),
            new ImportedCcdaViewFilter(),
            new ValidationErrorsCcdaFilter(),
        ];
    }

    public static function label()
    {
        return 'CCDAs';
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
