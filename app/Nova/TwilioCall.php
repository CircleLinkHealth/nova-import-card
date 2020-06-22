<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Nova\Actions\ExportTwilioCalls;
use App\Nova\Filters\TimestampFilter;
use App\Nova\Filters\TwilioCallSourceFilter;
use App\Nova\Metrics\TwilioCallCosts;
use App\Nova\Metrics\TwilioCallDuration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Titasgailius\SearchRelations\SearchesRelations;

class TwilioCall extends Resource
{
    use SearchesRelations;

    const CURRENCY_PRECISION          = 4;
    const TWILIO_JS_FLAT_FEE_PER_MIN  = 0.0040;
    const TWILIO_US_CALL_COST_PER_MIN = 0.0130;

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\TwilioCall::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'source',
        'from',
        'to',
        'inbound_user_id',
        'outbound_user_id',
        'call_status',
        'dial_call_status',
        'conference_friendly_name',
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'inboundEnrollee' => ['first_name', 'last_name'],
        'inboundUser'     => ['display_name', 'first_name', 'last_name'],
        'outboundUser'    => ['display_name', 'first_name', 'last_name'],
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $with = ['inboundEnrollee', 'inboundUser', 'outboundUser'];

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new ExportTwilioCalls(),
        ];
    }

    /**
     * @return bool
     */
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

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            new TwilioCallDuration('RN Call Duration', TwilioCallSourceFilter::PATIENT_CALL),
            new TwilioCallCosts('RN Call Costs', TwilioCallSourceFilter::PATIENT_CALL, 2),
            new TwilioCallDuration('CA Call Duration', TwilioCallSourceFilter::ENROLMENT_DASHBOARD),
            new TwilioCallCosts('CA Call Costs', TwilioCallSourceFilter::ENROLMENT_DASHBOARD, 2),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        $twilioJsFlatFee = self::TWILIO_JS_FLAT_FEE_PER_MIN;
        $twilioCallCost  = self::TWILIO_US_CALL_COST_PER_MIN;

        return [
            ID::make()->sortable(),

            Text::make('Twilio ID', 'call_sid')->hideFromIndex(true),

            Text::make('Direction', 'direction'),

            Text::make('Source', 'source'),

            Text::make('From', 'from')->sortable(),

            Text::make('RN/CA', 'outboundUser.display_name')->sortable(),

            Text::make('To', 'to')->sortable(),

            Text::make('Patient', function ($row) {
                if ($row->inboundUser) {
                    return $row->inboundUser->display_name;
                }
                if ($row->inboundEnrollee) {
                    return $row->inboundEnrollee->first_name.' '.$row->inboundEnrollee->last_name;
                }

                return null;
            })->sortable(),

            Text::make('Call Status', function ($row) {
                return $row->dial_call_status ?? $row->call_status;
            })->sortable(),

            Text::make('Call Duration (round up)', function ($row) {
                $minutes = ceil($row->call_duration / 60);

                return "${minutes}m";
            })->hideFromDetail(true)->sortable(),

            Text::make('API Duration (includes connection time)', function ($row) {
                return secondsToMMSS($row->call_duration ?? 0);
            })->hideFromIndex(true),

            Text::make("API Cost ($$twilioJsFlatFee/min)", function ($row) {
                $cost = $this->getCostFormatted($row->call_duration, self::TWILIO_JS_FLAT_FEE_PER_MIN, self::CURRENCY_PRECISION);

                return "$$cost";
            })->hideFromIndex(true),

            Text::make('Call Duration', function ($row) {
                return secondsToMMSS($row->dial_conference_duration ?? 0);
            })->hideFromIndex(true),

            Text::make("Call Cost ($$twilioCallCost/min)", function ($row) {
                $cost = $this->getCostFormatted($row->dial_conference_duration, self::TWILIO_US_CALL_COST_PER_MIN, self::CURRENCY_PRECISION);

                return "$$cost";
            })->hideFromIndex(true),

            Text::make('Total Cost', function ($row) {
                $fee = $this->getCost($row->call_duration, self::TWILIO_JS_FLAT_FEE_PER_MIN);
                $cost = $this->getCost($row->dial_conference_duration, self::TWILIO_US_CALL_COST_PER_MIN);
                $total = $this->formatCost(round($fee + $cost, self::CURRENCY_PRECISION), self::CURRENCY_PRECISION);

                return "$$total";
            })->sortable(),

            Boolean::make('Conference Call', 'in_conference'),

            Text::make('Note', function ($row) {
                $link = "<a href='https://www.twilio.com/console/voice/calls/logs/$row->call_sid' target='_blank'>Twilio Console</a>";

                return "<span>Cost is an estimate. For the exact amount please open $link. Total Cost is the sum of Cost in this page and Cost in Child Call(s).</span>";
            })->asHtml()->hideFromIndex(true),
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
            new TwilioCallSourceFilter(),
            new TimestampFilter('From', 'created_at', 'from', Carbon::now()->startOfMonth()),
            new TimestampFilter('To', 'created_at', 'to', Carbon::now()->endOfMonth()),
        ];
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

    private function formatCost($cost, int $precision)
    {
        return number_format($cost, $precision);
    }

    private function getCost(?int $durationSecs, float $cost, int $precision = null)
    {
        $result = ceil((int) $durationSecs / 60) * $cost;

        return $precision ? round($result, $precision) : $result;
    }

    private function getCostFormatted(?int $durationSecs, float $cost, int $precision)
    {
        $cost = $this->getCost($durationSecs, $cost, $precision);

        return $this->formatCost($cost, $precision);
    }
}
