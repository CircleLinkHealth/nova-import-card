<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ArchiveUnresolvedCallback;
use App\Nova\Filters\UnresolvedCallbacksFilter;
use App\Nova\Filters\UnresolvedCallbacksRangeFilter;
use CircleLinkHealth\SharedModels\Entities\UnresolvedCallbacksView;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class UnresolvedPostmarkCallbackResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = UnresolvedCallbacksView::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'postmark_id',
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
            new ArchiveUnresolvedCallback(),
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
        return true;
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
            Text::make('inbound callback id', 'postmark_id')
                ->hideWhenUpdating()
                ->sortable(),

            Date::make('date', 'date')
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('matched user id', 'matched_user_id')
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('matched patient name', 'matched_user_name')
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('reason', 'unresolved_reason')
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('patients match ids', 'other_possible_matches')
                ->hideWhenUpdating()
                ->sortable(), // Need to stringify this json

            Textarea::make('inbound callback', 'inbound_data')
                ->hideWhenUpdating()
                ->hideFromIndex(),

            Boolean::make('resolved to CC callback', 'resolved')
                ->hideWhenUpdating()
                ->sortable(),

            Text::make('callback id', 'call_id')
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->sortable(),

            Boolean::make('manually assigned callback to ca', 'assigned_to_ca')
                ->hideWhenUpdating()
                ->sortable(),

            Boolean::make('archived', 'manually_resolved')
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
            new UnresolvedCallbacksRangeFilter(),
            new UnresolvedCallbacksFilter(),
        ];
    }

    /**
     * @return string
     */
    public static function label()
    {
        return 'Unresolved Inbound Callbacks';
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
