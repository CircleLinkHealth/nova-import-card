@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }

            .hours-behind {
                margin-top: 3px;
                margin-bottom: 3px;
            }

            .ops-csv {
                margin-top: 13px;
                text-align: right;
            }

            .row.vdivide [class*='col-']:not(:last-child):after {
                background: #e0e0e0;
                width: 1px;
                content: "";
                display: block;
                position: absolute;
                top: 0;
                bottom: 0;
                right: 0;
                min-height: 70px;
            }

            .nav-tabs > li, .nav-pills > li {
                float: none;
                display: inline-block;
                *display: inline; /* ie7 fix */
                zoom: 1; /* hasLayout ie7 trigger */
            }

            .nav-tabs, .nav-pills {
                text-align: center;
            }

            .table td {
                text-align: center;
            }

            table {
                white-space: nowrap;
            }

            .panel-body {
                overflow-x: auto;
            }

            .table {
                white-space: nowrap;
            }

            .color-green {
                color: green;
            }

            .color-red {
                color: red;
            }

        </style>
    @endpush

    <div class="container">
{{--        Currently not in use, remove until and if needed--}}
{{--        <div class="col-md-12">--}}
{{--            @include('admin.opsDashboard.panel')--}}
{{--        </div>--}}
        <div class="col-md-4">
            <form action="{{route('OpsDashboard.index')}}" method="GET">
                <div class="form-group">
                    <div class="col-md-12">
                        <article>Active Patients as of @if($dateGenerated){{$dateGenerated->toTimeString()}}@else 11pm
                            ET @endif on:
                        </article>
                    </div>
                    <div class="col-md-8">
                        <input id="date" type="date" name="date" value="{{$date->toDateString()}}"
                               max="{{$maxDate->toDateString()}}" required class="form-control">
                    </div>
                    <div class="col-md-4">
                        <input type="submit" value="Submit" class="btn btn-info">
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <div class="col-md-12">
                    <span>&#8203;</span>
                </div>
                <div class="col-md-12">
                    <div class="ops-csv">
                        <a class="excel-export btn btn-info" data-href="{{route('OpsDashboard.dailyCsv')}}">Generate CSV</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="col-md-3">
                <div class="hours-behind">
                    <span>Hours Behind : </span>
                    <span class="label label-info">{{$hoursBehind}}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        @if($rows != null)
            <div class="panel panel-default">
                {{--<div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}}</div>--}}
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                        <thead>
                        <tr>
                            <th>Active Accounts</th>
                            <th>0 mins</th>
                            <th>0-5</th>
                            <th>5-10</th>
                            <th>10-15</th>
                            <th>15-20</th>
                            <th>20+</th>
                            <th>20+ BHI</th>
                            <th>Total</th>
                            <th>Prior Day totals</th>
                            <th>Added</th>
                            <th>Unreachable</th>
                            <th>Paused</th>
                            <th>Withdrawn</th>
                            <th>DELTA</th>
                            <th>G0506 To Enroll</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $key => $value)
                            @if($key == 'CircleLink Total')
                                <div class="row vdivide">
                                    <tr class="table-info">
                                        <td><strong>{{$key}}</strong></td>
                                        <td>{{$value['0 mins']}}</td>
                                        <td>{{$value['0-5']}}</td>
                                        <td>{{$value['5-10']}}</td>
                                        <td>{{$value['10-15']}}</td>
                                        <td>{{$value['15-20']}}</td>
                                        <td>{{$value['20+']}}</td>
                                        @if(isset($value['20+ BHI']))
                                            <td>{{$value['20+ BHI']}}</td>
                                        @else
                                            <td>N/A</td>
                                        @endif
                                        <td>{{$value['Total']}}</td>
                                        <td>{{$value['Prior Day totals']}}</td>
                                        @if($value['Added'] != 0)
                                            <td class="color-green">{{$value['Added']}}</td>@else
                                            <td>{{$value['Added']}}</td>@endif
                                        @if($value['Unreachable'] != 0)
                                            <td class="color-red">-{{$value['Unreachable']}}</td>@else
                                            <td>{{$value['Unreachable']}}</td>@endif
                                        @if($value['Paused'] != 0)
                                            <td class="color-red">-{{$value['Paused']}}</td>@else
                                            <td>{{$value['Paused']}}</td>@endif
                                        @if($value['Withdrawn'] != 0)
                                            <td class="color-red">-{{$value['Withdrawn']}}</td>@else
                                            <td>{{$value['Withdrawn']}}</td>@endif
                                        <td>{{$value['Delta']}}</td>
                                        <td>{{$value['G0506 To Enroll']}}</td>
                                    </tr>
                                </div>
                            @else
                                <div class="row vdivide">
                                    <tr>
                                        <th>{{$key}}</th>
                                        <td>{{$value['0 mins']}}</td>
                                        <td>{{$value['0-5']}}</td>
                                        <td>{{$value['5-10']}}</td>
                                        <td>{{$value['10-15']}}</td>
                                        <td>{{$value['15-20']}}</td>
                                        <td>{{$value['20+']}}</td>
                                        @if(isset($value['20+ BHI']))
                                            <td>{{$value['20+ BHI']}}</td>
                                        @else
                                            <td>N/A</td>
                                        @endif
                                        <td @if(isset($value['report_updated_at'])) title="Report generated at: {{$value['report_updated_at']}}"@endif>{{$value['Total']}}</td>
                                        <td @if(isset($value['prior_day_report_updated_at'])) title="Prior day report generated at: {{$value['prior_day_report_updated_at']}}"@endif>{{$value['Prior Day totals']}}</td>
                                        @if($value['Added'] != 0)
                                            <td class="color-green">{{$value['Added']}}</td>@else
                                            <td>{{$value['Added']}}</td>@endif
                                        @if($value['Unreachable'] != 0)
                                            <td class="color-red">-{{$value['Unreachable']}}</td>@else
                                            <td>{{$value['Unreachable']}}</td>@endif
                                        @if($value['Paused'] != 0)
                                            <td class="color-red">-{{$value['Paused']}}</td>@else
                                            <td>{{$value['Paused']}}</td>@endif
                                        @if($value['Withdrawn'] != 0)
                                            <td class="color-red">-{{$value['Withdrawn']}}</td>@else
                                            <td>{{$value['Withdrawn']}}</td>@endif
                                        <td @if(isset($value['lost_added_calculated_using_revisions']) && $value['lost_added_calculated_using_revisions']== true)
                                            title="Calculated using Revisions" @endif>{{$value['Delta']}}</td>
                                        <td>{{$value['G0506 To Enroll']}}</td>
                                    </tr>
                                </div>
                            @endif


                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-4">
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="well well-sm">
                <div class="alert alert-danger" role="alert">
                    <article>No report found for {{$date->toDateString()}}. Please select another date, or generate CSV
                        report for today.
                    </article>
                </div>
            </div>
        @endif
    </div>
    @push('scripts')
        <script>
            $(function () {
                function setExcelExportHref(date) {
                    var href = $('.excel-export').attr('data-href') + '?date=' + date
                    $('.excel-export').attr('href', href)
                    return href
                }

                $("#date").change(function (date) {
                    // whatever you need to be done on change of the input field
                    setExcelExportHref($("#start_date").val())
                });


                setExcelExportHref($("#date").val())
            })

        </script>

    @endpush
@endsection


