<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

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
</style>
{{--HACK! Duplicating css both in @push, and in <style> above so it works both with PDF, and web--}}
@push('styles')
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
    </style>
@endpush

<div class="page-header">
    <h1>CircleLink Health
        <small>
            Time Report for <b>{{$nurseFullName}}</b> from {{$startDate}} to {{$endDate}}
        </small>
    </h1>
</div>


<div class="row">
    <div class="col-md-10">
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
                <td>${{$baseSalary}}</td>
                <td>
                    @if($changedToFixedRateBecauseItYieldedMore)
                        <span>Highest Total Used:</span>
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
                <td>${{$addedTimeAmount}}</td>
                <td>
                    @if($hasAddedTime)
                        <span>{{$addedTime}} hours @ {{$nurseHourlyRate}}/hr</span>
                    @endif
                </td>
            </tr>
            <tr class="borderless-table-invoices">
                <td class="text-bold-invoice">Bonuses:</td>
                <td>${{$bonus}}</td>
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
            <tr>
                <th style="width: 25%">Date</th>
                @if(!$variablePay)
                    <th style="width: 25%">Minutes</th>
                @endif

                <th style="width: 25%">Total Hours</th>
                @if($variablePay)
                    <th style="width: 25%">CCM Hours (${{$nurseHighRate}}/Hour)</th>
                    <th style="width: 25%">CCM Hours (${{$nurseLowRate}}/Hour)</th>
                @endif
            </tr>

            <tr>
                <td><b>Total Hours</b></td>

                @if(!$variablePay)
                    <td>{{$systemTimeInMinutes}}</td>
                @endif

                <td>{{$systemTimeInHours}}</td>


                @if($variablePay)
                    <td>{{$totalTimeTowardsCcm}}</td>
                    <td>{{$totalTimeAfterCcm}}</td>
                @endif
            </tr>

            @foreach($timePerDay as $date => $row)
                <tr>
                    <td><b>{{$date}}</b></td>

                    @if(!$variablePay)
                        <td>{{$row['minutes']}}</td>
                    @endif

                    <td>{{$row['hours']}}</td>

                    @if($variablePay)
                        <td>{{$row['towards']}}</td>
                        <td>{{$row['after']}}</td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
</div>