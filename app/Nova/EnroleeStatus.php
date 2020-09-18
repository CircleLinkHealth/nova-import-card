<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use App\Nova\Importers\EnroleeStatus as EnroleeStatusImporter;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Http\Request;
use Jubeki\Nova\Cards\Linkable\LinkableAway;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Sparclex\NovaImportCard\NovaImportCard;

class EnroleeStatus extends Resource
{
    //todo: DEPRECATE/REMOVE AS SOON AS WE SWITCH OVER TO CA PANEL
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group    = CpmConstants::NOVA_GROUP_ENROLLMENT;
    public static $importer = EnroleeStatusImporter::class;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Enrollee::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'provider_id',
        'practice_id',
        'mrn',
        'first_name',
        'last_name',
        'status',
        'attempt_count',
        'last_attempt_at',
        'updated_at',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'mrn';

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

    public function authorizedToForceDelete(Request $request)
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
        return [
            new NovaImportCard(self::class),
            (new LinkableAway())
                ->title('CSV Template')
                ->url('https://docs.google.com/spreadsheets/d/1hvA5ILwpDTX6D9rh81RUlMivhlfe7PVBDSwF6Td9-QI/edit?usp=sharing')
                ->subtitle('Click to download.')
                ->target('_self'),
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
            Text::make('First Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('Last Name')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Text::make('Address')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Number::make('MRN')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('integer'),

            Text::make('Status')
                ->sortable()
                ->creationRules('required', 'string')
                ->updateRules('string'),

            Number::make('Attempt Count')
                ->sortable()
                ->creationRules('required', 'integer')
                ->updateRules('integer'),

            Date::make('last_attempt_at')
                ->sortable()
                ->format('MM/DD/YYYY')->creationRules('required', 'date')
                ->updateRules('date'),

            Date::make('updated_at')
                ->sortable()
                ->format('MM/DD/YYYY')->creationRules('required', 'date')
                ->updateRules('date'),
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
        return 'Patients - Update Information';
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
