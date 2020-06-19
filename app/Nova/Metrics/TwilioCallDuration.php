<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use App\TwilioCall;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;

class TwilioCallDuration extends Value
{
    public $name = 'Twilio Call Duration';

    public $width = '1/2';

    /**
     * @var string
     */
    private $source;

    /**
     * TwilioCallDuration constructor.
     */
    public function __construct(string $name, string $source)
    {
        $this->name   = $name;
        $this->source = $source;
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

        $calculationStatement = DB::raw('ceil(call_duration / 60)');

        $previousValue = TwilioCall::whereBetween('created_at', $this->previousRange($request->range, $timezone))
            ->where('source', '=', $this->source)
            ->sum($calculationStatement);

        return $this->result(TwilioCall::whereBetween('created_at', $this->currentRange($request->range, $timezone))
            ->where('source', '=', $this->source)
            ->sum($calculationStatement))->previous($previousValue)->suffix('minutes');
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
        return 'twilio-call-duration-c-a';
    }
}
