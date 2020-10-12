<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ArchiveUnresolvedCallback;
use App\Nova\Filters\UnresolvedCallbacksFilter;
use App\Nova\Filters\UnresolvedCallbacksRangeFilter;
use App\UnresolvedCallbacksResourceModel;
use Circlelinkhealth\UnresolvedCallback\UnresolvedCallback;
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
    public static $model = UnresolvedCallbacksResourceModel::class;

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
            //            (new UnresolvedCallback()),
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
            Text::make('inbound id', 'postmark_id')
                ->readonly()
                ->sortable(),

            Date::make('date', 'date')
                ->readonly()
                ->sortable(),

            Text::make('matched user', 'matched_user_id')
                ->readonly()
                ->sortable(),

            Text::make('reason', 'unresolved_reason')
                ->readonly()
                ->sortable(),

            Text::make('matches', 'other_possible_matches')
                ->readonly()
                ->sortable(), // Need to stringify this json

            Textarea::make('inbound callback', 'inbound_data')
                ->readonly()
                ->hideFromIndex(),

            Boolean::make('resolved to callback', 'resolved')
                ->readonly()
                ->sortable(),

            Text::make('callback id', 'call_id')
                ->readonly()
                ->sortable(),

            Boolean::make('archived', 'manually_resolved')
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
            new UnresolvedCallbacksFilter()
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
