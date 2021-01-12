<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\SharedModels\Entities\TrixField;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Waynestate\Nova\CKEditor;

class CareAmbassadorScript extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = CpmConstants::NOVA_GROUP_ENROLLMENT;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = TrixField::class;

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
            ID::make()->sortable(),

            Select::make('Language')
                ->options([
                    'en' => 'English',
                    'es' => 'Spanish',
                ])->displayUsingLabels(),

            Select::make('Type')
                ->options([
                    TrixField::CARE_AMBASSADOR_SCRIPT                  => 'For Patients to Enroll',
                    TrixField::CARE_AMBASSADOR_UNREACHABLE_USER_SCRIPT => 'For Unreachable Users',
                ])->displayUsingLabels(),

            CKEditor::make('Body', 'body')
                ->options([
                    'height'  => 500,
                    'toolbar' => [
                        ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'HorizontalRule', 'SpecialChar', 'PageBreak'],
                        ['Bold', 'Italic', 'Underline', 'Strike'],
                        ['TextColor', 'BGColor', '-', 'RemoveFormat'],
                        '/',
                        ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
                        ['JustifyLeft', 'JustifyCenter', 'JustifyRight'],
                        ['Link', 'Unlink', 'Anchor'],
                        ['Format', 'FontSize'],
                    ],
                ])
                ->hideFromIndex(),
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

    public static function newModel()
    {
        $model = new static::$model();

        return $model;
    }
}
