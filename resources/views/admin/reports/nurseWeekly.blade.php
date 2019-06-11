@extends('partials.adminUI')
@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-heading">Nurse Performance Report</div>

                            <div class="dates">
                                {{$startOfWeek->format('l F jS')}} - {{max($days)->format('l F jS Y')}}
                            </div>

                            <div class="calendar-date">
                                @include('admin.reports.nursesWeeklyReportForm')
                            </div>

                            <div class="panel-body">
                                <table class="table table-striped" id="nurse_weekly">
                                    <thead>
                                    @include('admin.reports.nurseWeeklyReportHeadings')
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                $('#nurse_weekly').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        "url": '{!! route('admin.reports.nurse.weekly.data') !!}',
                        "type": "GET",
                        "data": function (d) {
                            d.date = '{{$date}}'
                        }
                    },
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'scheduledCalls', name: 'scheduledCalls'},
                        {data: 'actualCalls', name: 'actualCalls'},
                        {data: 'successful', name: 'successful'},
                        {data: 'unsuccessful', name: 'unsuccessful'},
                        {data: 'actualHours', name: 'actualHours'},
                        {data: 'committedHours', name: 'committedHours'},
                        {data: 'completionRate', name: 'completionRate'},
                        {data: 'efficiencyIndex', name: 'efficiencyIndex'},
                        {data: 'caseLoadNeededToComplete', name: 'caseLoadNeededToComplete'},
                        {data: 'hoursCommittedRestOfMonth', name: 'hoursCommittedRestOfMonth'},
                        {data: 'surplusShortfallHours', name: 'surplusShortfallHours'},
                        {data: 'caseLoadComplete', name: 'caseLoadComplete'},
                    ],
                });
            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    @endpush
@stop