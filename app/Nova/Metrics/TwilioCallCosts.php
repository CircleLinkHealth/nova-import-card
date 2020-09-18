<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\SharedModels\Entities\TwilioCall;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;

class TwilioCallCosts extends Value
{
    public $name = 'Twilio Call Costs';

    public $width = '1/2';

    /**
     * @var string
     */
    private $source;

    /**
     * TwilioCallCosts constructor.
     *
     * @param mixed $precision
     */
    public function __construct(string $name, string $source, $precision = 4)
    {
        parent::__construct();
        $this->name      = $name;
        $this->source    = $source;
        $this->precision = $precision;
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateInterval|\DateTimeInterface|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(1);
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $timezone = Nova::resolveUserTimezone($request) ?? $request->timezone;

        $fee  = \App\Nova\TwilioCall::TWILIO_JS_FLAT_FEE_PER_MIN;
        $cost = \App\Nova\TwilioCall::TWILIO_US_CALL_COST_PER_MIN;

        $calculationQuery = DB::raw("round(((ceil(call_duration / 60) * $fee) + (ceil(dial_conference_duration / 60) * $cost)), $this->precision)");

        $previousValue = TwilioCall::whereBetween('created_at', $this->previousRange($request->range, $timezone))
            ->where('source', '=', $this->source)
            ->sum($calculationQuery);

        $result = TwilioCall::whereBetween('created_at', $this->currentRange($request->range, $timezone))
            ->where('source', '=', $this->source)
            ->sum($calculationQuery);

        return $this->result($result)->previous($previousValue)->prefix('$')->allowZeroResult(true);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30      => '30 Days',
            60      => '60 Days',
            365     => '365 Days',
            'TODAY' => 'Today',
            'MTD'   => 'Month To Date',
            'QTD'   => 'Quarter To Date',
            'YTD'   => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return "twilio-call-costs-$this->source";
    }
}
