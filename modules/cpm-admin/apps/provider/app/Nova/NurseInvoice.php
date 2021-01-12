<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\GenerateNurseInvoice;
use Circlelinkhealth\InvoicesDownload\InvoicesDownload;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use NovaButton\Button;
use Titasgailius\SearchRelations\SearchesRelations;

class NurseInvoice extends Resource
{
    use SearchesRelations;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = \App\Constants::NOVA_GROUP_CARE_COACHES;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\NurseInvoices\Entities\NurseInvoice::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'month_year',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'nurse.user' => ['display_name', 'first_name', 'last_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = 'nurse.user';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new GenerateNurseInvoice())->canRun(fn () => true)->canSee(fn () => true),
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
            new InvoicesDownload(),
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
            BelongsTo::make('Care Coach', 'user', CareCoachUser::class)
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->searchable()
                ->prepopulate(),

            Text::make('Name', 'nurse.user.display_name')
                ->sortable()
                ->hideWhenCreating()
                ->readonly(true),

            Date::make('Month', 'month_year')->sortable(),

            Button::make('View Invoice')->link(route('nurseinvoices.admin.show', [$this->nurse->user_id, $this->id]), '_blank')->style('primary'),

            Button::make('View Breakdown')->link($this->getInvoiceBreakdownUrl(), '_blank')->style('info-link'),

            ID::make()->sortable(),
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
        return 'Invoices';
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

    private function getInvoiceBreakdownUrl()
    {
        $novaPath  = Nova::path();
        $invoiceId = $this->resource->id;

        return "$novaPath/resources/nurse-invoice-breakdown/$invoiceId";
    }
}
