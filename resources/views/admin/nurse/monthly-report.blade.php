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
        <div class="input-group input-group-sm">
            <form action="{{route('admin.reports.nurse.monthly')}}" method="GET">
                <div class="form-group">
                    <article>Select Month</article>
                    <input id="date" type="date" name="date" value="{{$date->toDateString()}}" required class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit" class="btn btn-info">
                </div>
            </form>
        </div>

    </div>
    <div class="container">
        <div class="panel panel-default">
            {{--<div class="panel-heading">CarePlan Manager Patient Totals for {{$date->toDateString()}}</div>--}}
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <thead>
                    <tr>
                        <th>Nurse</th>
                        <th>CCM Time</th>
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
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
