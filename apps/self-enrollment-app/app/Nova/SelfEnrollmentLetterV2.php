<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\PreviewLetter;
use App\Rules\CanSaveNewSelfEnrollmentDiyLetter;
use Cache;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use OptimistDigital\MultiselectField\Multiselect;
use R64\NovaFields\JSON;

class SelfEnrollmentLetterV2 extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = EnrollmentInvitationLetterV2::class;

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
        return [
            (new PreviewLetter($this->resource->practice_id))->showOnTableRow()
                ->confirmText('Continue to letter review?')
                ->confirmButtonText('Continue')
                ->cancelButtonText('Cancel'),
        ];
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
        $resource          = $this->resource;
        $letterId          = optional($resource)->id;
        $practiceProviders = [];

        if ($resource->practice) {
            $practiceProviders = $this->getProviders($resource->practice->id);
        }

        return [
            BelongsTo::make('Practice')
                ->readonly(function ($request) {
                    return $request->isUpdateOrUpdateAttachedRequest();
                })
                ->searchable(),

            Boolean::make('Active', 'is_active')
                ->rules(new CanSaveNewSelfEnrollmentDiyLetter(optional($resource->practice)->id, $letterId)),

            Images::make(EnrollmentInvitationLetterV2::MEDIA_COLLECTION_LOGO_NAME)
                ->setFileName(function ($originalFilename, $extension, $letterModel) {
                    return Str::slug($letterModel->id).'-logo.'.$extension;
                }),

            Images::make('enrollment_signatures', EnrollmentInvitationLetterV2::MEDIA_COLLECTION_SIGNATURE_NAME)
                ->customPropertiesFields([
                    Select::make('Provider Letter Signature', 'provider_signature_id')
                        ->options($practiceProviders),

                    Multiselect::make('Providers Under Same Signature')
                        ->options($practiceProviders)
                        ->reorderable()
                        ->saveAsJSON(),

                    Trix::make('Signatory title attributes', 'signatory_title_attributes'),
                ])
                ->setFileName(function ($originalFilename, $extension, $letterModel) {
                    return Str::slug($letterModel->id).'-signature.'.$extension;
                }),

            Trix::make('Letter', 'body')->alwaysShow()->rules('required'),

            JSON::make('Options', [
                Select::make('Logo Position')
                    ->options([
                        'center' => 'Center',
                        'left'   => 'Left',
                        'right'  => 'Right',
                    ]),

                Text::make('Logo Size')->placeholder('120'),
                Text::make('Logo Distance From Text')->placeholder('70'),
            ]),
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

    public function getProviders(int $practiceId): array
    {
        return Cache::remember("providers_of_practice_$practiceId", 5, function () use($practiceId) {
            return \CircleLinkHealth\Customer\Entities\Practice::getProviders($practiceId)
                ->pluck('display_name', 'id')
                ->all();
        });
    }

    public static function label()
    {
        return 'Create Self Enrollment Letter';
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
