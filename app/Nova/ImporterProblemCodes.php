<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Filters\CodesFlaggedForReview;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\PerformsQueries;

class ImporterProblemCodes extends Resource
{
    use PerformsQueries {
        applyOrderings as protected traitApplyOrderings;
    }
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Eligibility\MedicalRecordImporter\SnomedToCpmIcdMap::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'snomed_code',
        'snomed_name',
        'icd_10_code',
        'icd_10_name',
        'icd_9_code',
        'icd_9_name',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = 'cpmProblem';

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
            BelongsTo::make('Problem', 'cpmProblem', CpmProblem::class)
                ->sortable()
                ->nullable(),

            Number::make('Snomed Code')->sortable()->nullable(),
            Text::make('Snomed Name')->sortable()->nullable(),
            Text::make('ICD 10 Code')->sortable()->nullable(),
            Text::make('ICD 10 Name')->sortable()->nullable(),
            Text::make('ICD 9 Code')->sortable()->nullable(),
            Text::make('ICD 9 Name')->sortable()->nullable(),
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
            new CodesFlaggedForReview(),
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

    protected static function applyOrderings($query, array $orderings)
    {
        //Sort my cpmProblem.name
        if (array_key_exists('cpmProblem', $orderings)) {
            $query->leftJoin('cpm_problems', 'cpm_problems.id', '=', 'snomed_to_cpm_icd_maps.cpm_problem_id');
            $orderings['cpm_problems.name'] = $orderings['cpmProblem'];
            unset($orderings['cpmProblem']);
        }

        return self::traitApplyOrderings($query, $orderings);
    }
}
