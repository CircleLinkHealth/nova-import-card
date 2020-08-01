@extends('partials.adminUI')

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

            table { white-space: nowrap; }
        </style>
    @endpush

    <div class="container">
        <h3 align="center">Nurse Monthly Report</h3>
        <div>
            <form action="{{route('admin.reports.nurse.monthly')}}" method="GET">
                <div class="row form-group">
                    <div class="col-sm-8">
                        <div class="row">
                            <div class="col-sm-12">
                                <label>Select Month:</label>
                            </div>
                            <div class="col-sm-4">
                                <v-datepicker name="date" class="form-control" format="yyyy-MM-dd" placeholder="YYYY-MM"
                                    minimum-view="month" maximum-view="year" required></v-datepicker>
                            </div>
                            <div class="col-sm-4">
                                <input type="submit" value="Submit" class="btn btn-success">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-right">
                        <a href="{{route('admin.reports.nurse.monthly')}}?{{ request()->getQueryString() }}&excel" class="btn btn-info">Export as Excel</a>
                    </div>
                    
                </div>
            </form>
        </div>

    </div>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <h5 align="center">Showing results for: {{$date->format('M, Y')}}</h5>
                    <thead>
                    <tr>
                        <th>Nurse</th>
                        <th>CCM Time (HH:MM:SS)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $key => $value)
                            <div class="row vdivide">
                                <tr>
                                    <th>{{$key}}</th>
                                    <td>{{$value}}</td>
                                </tr>
                            </div>
                    @endforeach
                    </tbody>

                </table>
            </div>

            <div class="panel-footer">
                <div class="row">
                    <div class="col-md-4">
                        {!! $rows->appends(Request::except('page'))->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
