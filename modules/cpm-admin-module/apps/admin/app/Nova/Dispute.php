<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ResolveInvoiceDispute;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use NovaButton\Button;

class Dispute extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_CARE_COACHES;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\SharedModels\Entities\Dispute::class;

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
    public static $title = 'id';
    public static $with  = 'disputable';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ResolveInvoiceDispute())->canRun(function ($request) {
                return $request->user()->isAdmin();
            })->canSee(function ($request) {
                return $request->user()->isAdmin();
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
        $disputable = optional($this->disputable);

        return [
            BelongsTo::make('Care Coach', 'user', CareCoachUser::class)->hideWhenUpdating()->readonly(),

            Button::make('View Invoice')
                ->link(route('nurseinvoices.admin.show', [$this->user_id, $disputable->id]), '_blank')
                ->style('info')
                ->visible(null != $disputable->id),
            Boolean::make('Is Resolved?', 'is_resolved')->hideWhenUpdating(),
            Text::make('reason')->hideWhenUpdating(),
            Text::make('resolved_at')->hideWhenUpdating(),
            Text::make('resolved_by')->hideWhenUpdating(),
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
        return 'Invoice Disputes';
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
