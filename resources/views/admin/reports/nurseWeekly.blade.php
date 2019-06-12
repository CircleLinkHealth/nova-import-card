@extends('partials.adminUI')
@section('content')
    @push('styles')
        @include('admin.reports.partials.nursesWeeklyreportTableStyles')
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Nurse Performance Report</div>

                <div class="dates">
                    {{$startOfWeek->format('l F jS')}} - {{max($days)->format('l F jS Y')}}
                </div>

                <div class="calendar-date">
                    @include('admin.reports.nursesWeeklyReportForm')
                </div>

                <div class="zui-wrapper">
                    <div class="zui-scroller">

                        <table class="zui-table">
                            <thead>
                            @include('admin.reports.nurseWeeklyReportHeadings')
                            </thead>

                            <tbody>
                            @include('admin.reports.nurseWeeklyReportBody')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
