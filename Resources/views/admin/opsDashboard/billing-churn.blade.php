@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
            .row.vdivide [class*='col-']:not(:last-child):after {
                background: #e0e0e0;
                width: 1px;
                content: "";
                display:block;
                position: absolute;
                top:0;
                bottom: 0;
                right: 0;
                min-height: 70px;
            }
            .nav-tabs > li, .nav-pills > li {
                float:none;
                display:inline-block;
                *display:inline; /* ie7 fix */
                zoom:1; /* hasLayout ie7 trigger */
            }

            .nav-tabs, .nav-pills {
                text-align:center;
            }

            .table td {
                text-align: center;
            }

            .panel-body {
                overflow-x: auto;
            }

            table { white-space: nowrap; }

            .color-green {
                color: green;
            }

            .color-red {
                color: red;
            }
        </style>
    @endpush
    <h3 align="center">Billing Churn Dashboard</h3>
    <div class="container">
        {{--<div class="col-md-12">--}}
            {{--@include('admin.opsDashboard.panel')--}}
        {{--</div>--}}
        <div class="input-group input-group-sm">
            <form action="{{route('OpsDashboard.billingChurn')}}" method="GET">
                <div class="form-group">
                    <div class="form-group">
                        Months to show:
                        <select name="months">
                            <option name="months" value="all">All</option>
                            <option name="months" value="1">1</option>
                            <option name="months" value="2">2</option>
                            <option name="months" value="3">3</option>
                            <option name="months" value="4">4</option>
                            <option name="months" value="5">5</option>
                            <option name="months" value="6">6</option>
                            <option name="months" value="7">7</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit" class="btn btn-info">
                </div>
            </form>
        </div>
    </div>
    <div class="container">
        <div class="panel panel-default">
            {{--<div class="panel-heading">Billing Churn Dashboard</div>--}}
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <thead>
                    <tr>
                        <th> </th>
                        @foreach($months as $month)
                            <th>{{$month->format('F, Y')}}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <div class="row vdivide">
                        <tr>
                            <td><strong>CircleLink Total</strong> billed:</td>
                            @foreach($total['Billed'] as $month => $count)

                                <td>{{$count}}</td>

                            @endforeach
                        </tr>
                        <tr>
                            <td>Added to billing:</td>

                            @foreach($total['Added to Billing'] as $month => $count)

                                <td class="color-green">{{$count}}</td>

                            @endforeach

                        </tr>
                        <tr>
                            <td>Lost from Billing</td>

                            @foreach($total['Lost from Billing'] as $month => $count)

                                <td class="color-red">{{$count}}</td>

                            @endforeach

                        </tr>
                        <tr>
                            <td> </td>
                        </tr>
                    </div>
                    @foreach($rows as $practice => $patients)
                        
                        <?php
                            $billedMonths       = $patients['Billed'];
                            $profitBilledMonths = $patients['Added to Billing'];
                            $lossBilledMonths   = $patients['Lost from Billing'];
                        ?>

                        <div class="row vdivide">
                            <tr>
                                <td><strong>{{$practice}}</strong> billed:</td>
                                
                                @foreach($patients['Billed'] as $month => $count)

                                    <td>{{ ($count == 0 && !$profitBilledMonths[$month] && !$lossBilledMonths[$month]) ? '' : $count}}</td>

                                @endforeach
                            </tr>
                            <tr>
                                <td>Added to billing:</td>

                                @foreach($patients['Added to Billing'] as $month => $count)

                                    <td class="color-green">{{ ($count == 0 && !$billedMonths[$month] && !$lossBilledMonths[$month]) ? '' : $count}}</td>

                                @endforeach

                            </tr>
                            <tr>
                                <td>Lost from Billing</td>

                                @foreach($patients['Lost from Billing'] as $month => $count)

                                    <td class="color-red">{{ ($count == 0 && !$billedMonths[$month] && !$profitBilledMonths[$month]) ? '' : $count}}</td>

                                @endforeach

                            </tr>
                            <tr>
                                <td> </td>
                            </tr>
                        </div>
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
    </div>


@endsection


