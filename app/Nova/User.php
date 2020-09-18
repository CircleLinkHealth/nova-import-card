<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Http\Controllers\API\PracticeStaffController;
use App\Nova\Actions\UserEnroll;
use App\Nova\Actions\UserUnreachable;
use App\Nova\Actions\UserWithdraw;
use App\Nova\Filters\UserPracticeFilter;
use App\Nova\Filters\UserRoleFilter;
use CircleLinkHealth\Customer\Entities\User as CpmUser;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Titasgailius\SearchRelations\SearchesRelations;

class User extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \CircleLinkHealth\Customer\CpmConstants::NOVA_GROUP_ADMIN;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = CpmUser::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'display_name',
        'email',
        'first_name',
        'last_name',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'primaryPractice' => ['display_name'],
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
        return [
            new UserUnreachable(),
            new UserEnroll(),
            new UserWithdraw(),
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return true;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    public static function availableForNavigation(Request $request)
    {
        return auth()->user()->isAdmin();
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

            Text::make('First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Last Name', 'last_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Suffix', 'suffix')
                ->rules('required', 'max:255'),

            Text::make('Email', 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password', 'password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),

            Text::make('Practice', 'primaryPractice.display_name'),

            Text::make('Role', function () {
                $role = $this->practiceOrGlobalRole();
                if ( ! $role) {
                    return 'n/a';
                }

                return UserRoleFilter::ROLES_MAP[$role->name] ?? 'n/a';
            }),

            Text::make('Edit', function () {
                if ($this->hasRole(PracticeStaffController::PRACTICE_STAFF_ROLES)) {
                    $practiceSlug = $this->primaryPractice->name;
                    $url = route('provider.dashboard.manage.staff', ['practiceSlug' => $practiceSlug]);
                } else {
                    $url = route('admin.users.edit', ['id' => $this->id]);
                }

                return $this->getEditButton($url);
            })->asHtml(),
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
            new UserRoleFilter(),
            new UserPracticeFilter(),
        ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $withRelations = ['primaryPractice', 'roles', 'practices'];

        /** @var \CircleLinkHealth\Customer\Entities\User $user */
        $user = auth()->user();
        if ($user->isAdmin()) {
            return $query->with($withRelations);
        }

        $programIds = $user->viewableProgramIds();

        return $query->with($withRelations)
            ->whereHas('practices', function ($q) use ($programIds) {
                $q->whereIn('practice_role_user.program_id', $programIds);
            });
    }

    public static function label()
    {
        return 'All Users';
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

    public static function singularLabel()
    {
        return 'User';
    }

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout()
    {
        return false;
    }

    private function getEditButton($url)
    {
        //this is a hack to hide the edit button of the resource, so that only the custom one below will be visible
        $styleHack = "<style>
a[dusk='{$this->id}-edit-button'], a[dusk='edit-resource-button'] {
    display: none;
}
</style>";

        return $styleHack.'<a target="_blank" href="'.$url.'" class="inline-flex cursor-pointer text-70 hover:text-primary mr-3 has-tooltip">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" aria-labelledby="edit" role="presentation" class="fill-current"><path d="M4.3 10.3l10-10a1 1 0 0 1 1.4 0l4 4a1 1 0 0 1 0 1.4l-10 10a1 1 0 0 1-.7.3H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 .3-.7zM6 14h2.59l9-9L15 2.41l-9 9V14zm10-2a1 1 0 0 1 2 0v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2h6a1 1 0 1 1 0 2H2v14h14v-6z"></path></svg>
</a>';
    }
}
