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
    public static $model = \CircleLinkHealth\NurseInvoices\Entities\NurseInvoice::class;

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
            $ccmText = '';
            foreach (($patientVisits['ccm'] ?? []) as $date => $feePayAndCount) {
                $totalForPatient += $feePayAndCount['count'];
                $fee = $formatter->formatCurrency($feePayAndCount['fee'], 'USD');
                $ccmText .= "{$date}: {$feePayAndCount['count']} visit(s) - {$fee}<br/>";
            }

            $bhiText = '';
            foreach (($patientVisits['bhi'] ?? []) as $date => $feePayAndCount) {
                $totalForPatient += $feePayAndCount['count'];
                $fee = $formatter->formatCurrency($feePayAndCount['fee'], 'USD');
                $bhiText .= "{$date}: {$feePayAndCount['count']} visit(s) - {$fee}<br/>";
            }

            $pcmText = '';
            foreach (($patientVisits['pcm'] ?? []) as $date => $feePayAndCount) {
                $totalForPatient += $feePayAndCount['count'];
                $fee = $formatter->formatCurrency($feePayAndCount['fee'], 'USD');
                $pcmText .= "{$date}: {$feePayAndCount['count']} visit(s) - {$fee}<br/>";
            }

            $fields[] = Text::make("$patientId [$totalForPatient visits]", function ($invoice) use ($ccmText, $bhiText, $pcmText) {
                $result = '';
                if ( ! empty($ccmText)) {
                    $result .= "<strong>CCM</strong><br/>$ccmText";
                }
                if ( ! empty($bhiText)) {
                    $result .= "<strong>BHI</strong><br/>$bhiText";
                }
                if ( ! empty($pcmText)) {
                    $result .= "<strong>PCM</strong><br/>$pcmText";
                }

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
        foreach (($data['visits'] ?? []) as $patientId => $patientVisits) {
            $current        = $allVisits->get($patientId, []);
            $current['ccm'] = $patientVisits;
            $allVisits->put($patientId, $current);
        }

        foreach (($data['bhiVisits'] ?? []) as $patientId => $patientVisits) {
            $current        = $allVisits->get($patientId, []);
            $current['bhi'] = $patientVisits;
            $allVisits->put($patientId, $current);
        }

        foreach (($data['pcmVisits'] ?? []) as $patientId => $patientVisits) {
            $current        = $allVisits->get($patientId, []);
            $current['pcm'] = $patientVisits;
            $allVisits->put($patientId, $current);
        }

        return $allVisits;
    }
}
