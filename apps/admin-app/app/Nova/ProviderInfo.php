<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\Entities\ProviderInfo as ProviderInfoModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class ProviderInfo extends Resource
{
    use SearchesRelations;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = ProviderInfoModel::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'user_id',
        'specialty',
        'pronunciation',
        'sex',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'user' => ['display_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user_id';

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
        return true;
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
            BelongsTo::make('user')
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->hideFromIndex()
                ->sortable(),

            Text::make('Name', 'user.display_name')
                ->hideFromDetail()
                ->hideWhenCreating()
                ->readonly(true),

            Text::make('Prefix')
                ->sortable()
                ->updateRules('nullable', 'string'),

            Text::make('Pronunciation')
                ->sortable()
                ->updateRules('nullable', 'string'),

            Text::make('Sex')
                ->sortable()
                ->updateRules('nullable', 'string'),

            Text::make('Specialty')
                ->sortable()
                ->updateRules('nullable', 'string'),
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

    public static function label()
    {
        return 'Provider Details';
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
