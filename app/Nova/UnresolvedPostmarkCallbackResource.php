<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\UnresolvedCallbacksResourceModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;

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
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('inbound id', 'postmark_id')->sortable(),
            Text::make('matched user', 'matched_user_id')->sortable(),
            Text::make('reason', 'unresolved_reason'),
            Text::make('possible matches', 'other_possible_matches', function ($usersId) {
                $links = collect();
                foreach (json_decode($usersId) as $userId) {
                    $links->push(link_to_route('patient.careplan.print', $userId, $userId)->toHtml());
                }
                return array_values($links->toArray());
            })->asHtml(),

            Boolean::make('resolved', 'resolved')->sortable(),
            Text::make('resolved callback id', 'call_id'),
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
