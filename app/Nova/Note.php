<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Filters\CarePlanStatusFilter;
use App\Nova\Filters\NoteStatusFilter;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;
use Titasgailius\SearchRelations\SearchesRelations;

class Note extends Resource
{
    use SearchesRelations;

    private const CAREPLAN_STATUS_MAP = [
        ''                          => 'n/a',
        CarePlan::DRAFT             => 'Draft',
        CarePlan::QA_APPROVED       => 'QA Approved',
        CarePlan::RN_APPROVED       => 'RN Approved',
        CarePlan::PROVIDER_APPROVED => 'Provider Approved',
    ];

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
    public static $group = \App\Constants::NOVA_GROUP_CARE_COACHES;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Note::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'patient_id',
        'author_id',
        'status',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'author'  => ['display_name'],
        'patient' => ['display_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = [
        'patient.carePlan',
        'call',
    ];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new DownloadExcel(),
        ];
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
            ID::make()->sortable(),

            BelongsTo::make('Care Coach', 'author', CareCoachUser::class)
                ->sortable()
                ->readonly(),

            BelongsTo::make('Patient', 'patient', CareCoachUser::class)
                ->sortable()
                ->readonly(),

            Text::make('Type', 'type')
                ->sortable()
                ->readonly(),

            Textarea::make('Body', 'body')
                ->readonly(),

            Text::make('Status', 'status')
                ->sortable()
                ->readonly(),

            Text::make('CarePlan Status', function ($item) {
                return self::CAREPLAN_STATUS_MAP[$item->patient->carePlan->status ?? ''];
            })
                ->sortable()
                ->readonly(),

            Boolean::make('Has Call', function ($item) {
                return (bool) $item->call;
            })
                ->readonly(),

            Boolean::make('Success Story', 'success_story')
                ->readonly(),

            DateTime::make('Created At', 'created_at')
                ->sortable()
                ->readonly(),

            DateTime::make('Updated At', 'updated_at')
                ->sortable()
                ->readonly(),
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
            new NoteStatusFilter(\App\Note::STATUS_DRAFT),
            new CarePlanStatusFilter(CarePlan::QA_APPROVED),
        ];
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
