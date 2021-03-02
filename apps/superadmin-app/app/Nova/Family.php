<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\AddFamilyMemberAction;
use App\Nova\Actions\RemoveFamilyMemberAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class Family extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\Customer\Entities\Family::class;

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

    public static $with = [
        'patients.user',
    ];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        /** @var \CircleLinkHealth\Customer\Entities\Family $resource */
        $resource     = $this->resource;
        $memberFields = $resource->patients->mapWithKeys(function ($p) {
            return [$p->user->id => $p->user->display_name];
        })->toArray();

        return [
            (new AddFamilyMemberAction())
                ->onlyOnDetail(true)
                ->canSee(function () {
                    return true;
                })
                ->canRun(function () {
                    return true;
                }),
            (new RemoveFamilyMemberAction($memberFields))
                ->onlyOnDetail(true)
                ->canSee(function () {
                    return true;
                })
                ->canRun(function () {
                    return true;
                }),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return true;
    }

    public function authorizedToDelete(Request $request)
    {
        return true;
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
        /** @var \CircleLinkHealth\Customer\Entities\Family $resource */
        $resource = $this->resource;

        $memberFields = $resource->patients->map(function ($p) {
            return Text::make('Member', function () use ($p) {
                return $p->user->getFullName();
            });
        });

        while ($memberFields->count() < 3) {
            $memberFields->push(Text::make('Member', function () {
                return '';
            }));
        }

        return [
            ID::make(__('ID'), 'id')->sortable(),

            ...$memberFields,
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
