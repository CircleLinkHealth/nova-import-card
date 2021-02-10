<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\DownloadCsv;
use App\Nova\Actions\ImportCcdaAction;
use App\Nova\Actions\ReimportCcda;
use App\Nova\Filters\CcdaView\ImportedCcdaViewFilter;
use App\Nova\Filters\CcdaView\ValidationErrorsCcdaFilter;
use App\Nova\Filters\CpmDateFilter;
use App\Nova\Filters\PracticeFilter;
use CircleLinkHealth\Customer\CpmConstants;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

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
    public static $model = \CircleLinkHealth\SharedModels\Entities\Ccda::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'patient_id',
        'patient_mrn',
        'patient_first_name',
        'patient_last_name',
        'dm_from',
        'patient_dob',
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
                'patient_first_name',
                'patient_last_name',
                'patient_mrn',
                'patient_dob',
                'provider',
                'practice_display_name',
                'patient_id',
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
                if ( ! $row->patient_id) {
                    return '';
                }

                return link_to(env('CPM_PROVIDER_APP_URL')."manage-patients/{$row->patient_id}/view-careplan", 'View', [$row->patient_id])->toHtml();
            })->asHtml(),
            Text::make('First Name', 'patient_first_name')->sortable(),
            Text::make('Last Name', 'patient_last_name')->sortable(),
            Date::make('DOB', 'patient_dob')->sortable(),
            Text::make('Mrn', 'patient_mrn')->sortable(),
            Text::make('Provider', 'provider_name')->sortable(),
            Text::make('Practice', 'practice_display_name')->sortable(),
            //            Text::make('Nurse', 'nurse_user_name')->sortable(),
            Text::make('From (DM)', 'dm_from')->sortable(),
            DateTime::make('Created At', 'created_at')->sortable(),
            Code::make('Errors', 'validation_checks')->json(),
            Text::make('Source', 'source')->sortable(),
            Text::make('patient_id')->sortable(),
            ID::make('id')->sortable(),
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
            (new CpmDateFilter('ccdas.created_at'))->setName('Created on or after')->setOperator('>=')->setDefaultDate(now()->subWeeks(2)->toDateTimeString()),
            (new CpmDateFilter('ccdas.created_at'))->setName('Created before or on')->setOperator('<=')->setDefaultDate(now()->addDay()->toDateTimeString()),
            new ImportedCcdaViewFilter(),
            new ValidationErrorsCcdaFilter(),
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return parent::indexQuery($request, $query)
            ->join('users as providers', 'providers.id', '=', 'ccdas.billing_provider_id')
            ->join('practices', 'practices.id', '=', 'ccdas.practice_id')
            ->join('direct_mail_messages', 'direct_mail_messages.id', '=', 'ccdas.direct_mail_message_id')
            ->select([
                'ccdas.patient_first_name',
                'ccdas.patient_last_name',
                'ccdas.patient_dob',
                'ccdas.patient_mrn',
                'ccdas.created_at',
                'ccdas.source',
                'ccdas.patient_id',
                'ccdas.billing_provider_id',
                'ccdas.id',
                'providers.display_name as provider_name',
                'practices.display_name as practice_display_name',
                'direct_mail_messages.from as dm_from',
            ]);
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
