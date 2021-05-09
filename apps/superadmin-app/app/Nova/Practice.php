<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ExportPatientProblemCodes;
use App\Nova\Importers\PatientConsentLetters;
use CircleLinkHealth\ClhImportCardExtended\ClhImportCardExtended;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Practice extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_PRACTICES;

    public static $importer = PatientConsentLetters::class;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Practice::class;
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
    public static $title = 'display_name';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        $actions = [
            new ExportPatientProblemCodes(),
        ];

        //There is a known bug when adding ->canSee and ->canRun for actions that are queueable, this is a workaround
        if ($request->user()->isAdmin()) {
            $actions[] = (new Actions\FaxApprovedCarePlans())->onlyOnDetail();
        }

        return $actions;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            //todo: seems to be breaking for the moment
            //            ClhImportCardExtended::make(self::class, [
            //                Text::make('email')
            //                    ->withModel(\App\User::class, 'email')
            //                    ->inputRules(['required', 'email']),
            //            ], 'Create and Send Patient Consent Letters'),
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
            ID::make()->sortable(),

            Text::make('Name', 'display_name')->sortable(),

            Boolean::make('Is Active', 'active')->sortable(),

            Boolean::make('Is Demo', 'is_demo')->sortable(),
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
        return [];
    }
}
