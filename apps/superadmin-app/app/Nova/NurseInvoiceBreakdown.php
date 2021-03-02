<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use NumberFormatter;

class NurseInvoiceBreakdown extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \CircleLinkHealth\SharedModels\Entities\NurseInvoice::class;

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

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public static function availableForNavigation(Request $request)
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
        $fields = [
            ID::make(__('Invoice ID'), 'id'),

            Date::make('Month', function ($row) {
                return "{$row->month_year->monthName} {$row->month_year->year}";
            }),

            Text::make('Name', 'nurse.user.display_name'),
        ];

        $data = $this->resource->invoice_data ?? [];
        if ( ! isset($data['visits'])) {
            return $fields;
        }

        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $allVisits = $this->mergeVisitsToOneCollection($data);
        $allVisits->each(function ($patientVisits, $patientId) use (&$fields, $formatter) {
            $totalForPatient = 0;
            $texts = collect();
            foreach ($patientVisits as $csCode => $visits) {
                foreach ($visits as $date => $feePayAndCount) {
                    $totalForPatient += $feePayAndCount['count'];
                    $fee = $formatter->formatCurrency($feePayAndCount['fee'], 'USD');
                    $csCodeText = $texts->get($csCode, '');
                    $csCodeText .= "{$date}: {$feePayAndCount['count']} visit(s) - {$fee}<br/>";
                    $texts->put($csCode, $csCodeText);
                }
            }

            $fields[] = Text::make("$patientId [$totalForPatient visits]", function ($invoice) use ($texts) {
                $result = '';
                $texts->each(function ($text, $csCode) use (&$result) {
                    $friendlyName = \CircleLinkHealth\Customer\Entities\ChargeableService::getFriendlyName($csCode);
                    $result .= "<strong>$friendlyName</strong><br/>$text";
                });

                return $result;
            })->asHtml();
        });

        return $fields;
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

    public static function uriKey()
    {
        return 'nurse-invoice-breakdown';
    }

    private function mergeVisitsToOneCollection($data)
    {
        $allVisits = collect();
        foreach (($data['visits'] ?? []) as $patientId => $patientVisitsPerChargeableServiceCode) {
            foreach ($patientVisitsPerChargeableServiceCode as $csCode => $visits) {
                $current          = $allVisits->get($patientId, []);
                $current[$csCode] = $visits;
                $allVisits->put($patientId, $current);
            }
        }

        return $allVisits;
    }
}
