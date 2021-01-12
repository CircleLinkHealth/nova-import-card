<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Nova\TwilioCall;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportTwilioCalls extends DownloadExcel implements WithMapping
{
    use InteractsWithQueue;
    use Queueable;

    protected $headings = [
        'ID',
        'Twilio ID',
        'Direction',
        'Source',
        'From',
        'RN/CA',
        'To',
        'Patient ID',
        'Call Status',
        'API Duration',
        'API Duration Roundup',
        'API Cost',
        'Call Duration',
        'Call Duration Roundup',
        'Call Cost',
        'Total Cost',
    ];

    public function map($call): array
    {
        $apiDurationRoundup = ceil($call->call_duration / 60);
        $apiDurationCost    = $apiDurationRoundup * \App\Nova\TwilioCall::TWILIO_JS_FLAT_FEE_PER_MIN;

        $callDurationRoundup = ceil($call->dial_conference_duration / 60);
        $callDurationCost    = $callDurationRoundup * TwilioCall::TWILIO_US_CALL_COST_PER_MIN;

        return [
            $call->id,
            $call->call_sid,
            $call->direction,
            $call->source,
            $call->from,
            optional($call->outboundUser)->display_name,
            $call->to,
            $call->inbound_user_id ?? $call->inbound_enrollee_id,
            $call->dial_call_status ?? $call->call_status,
            secondsToMMSS($call->call_duration),
            number_format($apiDurationRoundup),
            number_format(round($apiDurationCost, 4), 4),
            secondsToMMSS($call->dial_conference_duration),
            number_format($callDurationRoundup),
            number_format(round($callDurationCost, 4), 4),
            number_format(round($apiDurationCost + $callDurationCost, 4), 4),
        ];
    }
}
