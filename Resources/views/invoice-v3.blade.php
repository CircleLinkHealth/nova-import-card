@if(isset($isPdf))
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
@endif

<style>
    body {
        background: #fff;
        color: #333;
        line-height: 1.3em;
        font-weight: 300;
    }

    .borderless-table-invoices, .borderless-table-invoices > * {
        border: none !important;
    }

    .cross-out {
        text-decoration: line-through;
    }

    .display-inline-block {
        display: inline-block;
    }

    .text-bold-invoice {
        font-weight: bold;
    }

    .invoice-page-header {
        padding-bottom: 9px;
        margin: -10px 0 20px;
        border-bottom: 1px solid #eee;
    }
</style>
{{--HACK! Duplicating css both in @push, and in <style> above so it works both with PDF, and web--}}
{{--NOTE! This is causing an Error showing in dev console:
Templates should only be responsible for mapping the state to the UI.
Avoid placing tags with side-effects in your templates,
such as <style>, as they will not be parsed.--}}
@push('styles')
    <style>
        h1 {
            font-size: 36px;
        }

        body, h1, h2, h3, h4, h5, h6 {
            font-family: "Arial", serif !important;
        }

        body {
            background: #fff;
            color: #333;
            line-height: 1.3em;
            font-weight: 300;
        }

        .borderless-table-invoices, .borderless-table-invoices > * {
            border: none !important;
        }

        .cross-out {
            text-decoration: line-through;
        }

        .display-inline-block {
            display: inline-block;
        }

        .text-bold-invoice {
            font-weight: bold;
        }

        .invoice-page-header {
            padding-bottom: 9px;
            margin: -10px 0 20px;
            border-bottom: 1px solid #eee;
        }
    </style>
@endpush

<div class="invoice-page-header">
    <h1>CircleLink Health
        <small>
            Time Report for <b>{{$nurseFullName}}</b> from {{$startDate}} to {{$endDate}}
        </small>
    </h1>
</div>
<div class="row">
    <div class="col-md-10" data-step="2" data-intro="The top part of the invoice shows a breakdown of your pay.">
        <table class="table borderless-table-invoices table-hover">
            <thead>
            <tr class="borderless-table-invoices">
                <th></th>
                <th class="text-bold-invoice">Amount ($)</th>
                <th>Note</th>
            </tr>
            </thead>
            <tbody>
            <tr class="borderless-table-invoices">
                <td class="text-bold-invoice">Base Pay:</td>
                <td>${{number_format($baseSalary, 2)}}</td>
                <td>
                    @if($changedToFixedRateBecauseItYieldedMore)
                        <span>Highest Total Pay Used:</span>
                    @endif
                    @if(is_array($formattedBaseSalary))
                        <span class="display-inline-block">{{$formattedBaseSalary['high']}}</span>
                        <span class="display-inline-block cross-out"><small
                                    class="cross-out">{{$formattedBaseSalary['low']}}</small></span>
                    @else
                        <span class="display-inline-block">{{$formattedBaseSalary}}</span>
                    @endif
                </td>
            </tr>
            <tr class="borderless-table-invoices">
                <td class="text-bold-invoice">Extra Time:</td>
                <td>${{number_format($addedTimeAmount, 2)}}</td>
                <td>
                    @if($hasAddedTime)
                        <span>{{$addedTime}} hours @ {{$nurseHourlyRate}}/hr</span>
                    @endif
                </td>
            </tr>
            <tr class="borderless-table-invoices">
                <td class="text-bold-invoice">Bonuses:</td>
                <td>${{number_format($bonus, 2)}}</td>
                <td></td>
            </tr>
            <tr class="borderless-table-invoices">
                <td class="text-bold-invoice">Total Due:</td>
                <td style="border-top: 2px solid black !important;">
                    {{$formattedInvoiceTotalAmount}}
                </td>
                <td>

                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered table-hover">
            <tr data-step="3" data-intro="This table shows how much time the system recorded for you per day.">
                <th style="width: 25%">Date</th>
                <th style="width: 25%">Total Time (hh:mm)</th>

                @if($variablePay)
                    @if(isset($altAlgoEnabled) && !$altAlgoEnabled)
                        <th style="width: 25%">CCM Hours (High Rates)</th>
                        <th style="width: 25%">CCM Hours (${{$nurseLowRate}}/Hour)</th>
                    @elseif(isset($visitsPerDayAvailable) && $visitsPerDayAvailable)
                        <th style="width: 25%">Visits</th>
                    @elseif (!isset($altAlgoEnabled))
                        <th style="width: 25%">CCM Hours (High Rate)</th>
                        <th style="width: 25%">CCM Hours (${{$nurseLowRate}}/Hour)</th>
                    @endif
                @endif
            </tr>

            <tr>
                <td><b>Total Time</b></td>

                <td>{{$formattedSystemTime}}</td>

                @if($variablePay)
                    @if(isset($altAlgoEnabled) && !$altAlgoEnabled)
                        <td>{{$totalTimeTowardsCcmInHours}}</td>
                        <td>{{$totalTimeAfterCcmInHours}}</td>
                    @elseif(isset($visitsPerDayAvailable) && $visitsPerDayAvailable)
                        <td>{{$visitsCount}}</td>
                    @elseif (!isset($altAlgoEnabled))
                        <td>{{$totalTimeTowardsCcmInHours ?? $totalTimeTowardsCcm}}</td>
                        <td>{{$totalTimeAfterCcmInHours ?? $totalTimeAfterCcm}}</td>
                    @endif
                @endif
            </tr>

            @foreach($timePerDay as $date => $row)
                <tr>
                    <td><b>{{$date}}</b></td>
                    @if(isset($isPdf))
                        <td>{{$row['formatted_time']}}</td>
                    @else
                        <td>
                            <nurse-invoice-daily-dispute
                                    :invoice-data="{{json_encode($row)}}"
                                    :invoice-id="{{$invoiceId}}"
                                    :day="{{json_encode(\Carbon\Carbon::parse($date)->copy()->toDateString())}}"
                                    :is-user-auth-to-daily-dispute="{{json_encode($isUserAuthToDailyDispute)}}"
                                    :can-be-disputed="{{json_encode($canBeDisputed)}}">
                            </nurse-invoice-daily-dispute>
                        </td>
                    @endif


                    @if($variablePay)
                        @if(isset($altAlgoEnabled) && !$altAlgoEnabled)
                            <td>{{$row['towards']}}</td>
                            <td>{{$row['after']}}</td>
                        @elseif(isset($visitsPerDayAvailable) && $visitsPerDayAvailable)
                            <td>{{$row['visits_count']}}</td>
                        @elseif (!isset($altAlgoEnabled))
                            <td>{{$row['towards']}}</td>
                            <td>{{$row['after']}}</td>
                        @endif
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
</div>