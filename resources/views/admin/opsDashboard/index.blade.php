@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush

    <div class="container">
        <h1 align="center">Patient Pipeline</h1>
        <div class="row">
            <div class="col-md-12">
                @include('admin.opsDashboard.tables.total-patients')
            </div>
        </div>
        <br>
        <hr><br>
        <div class="text-center">
            <div>
                <form action="{{route('OpsDashboard.pausedPatientList')}}">
                    <h3 class="ops-dboard-title">Generate Paused Patient List</h3>
                    <br>
                    From:
                    <input type="date" name="fromDate" required>
                    To:
                    <input type="date" name="toDate" required>

                    <br>
                    <input align="center" type="submit" value="Submit">
                </form>
            </div>

        </div>
        <br>
        <hr><br>
        <div>
            <div class="col-md-12">
                @include('admin.opsDashboard.tables.patients-by-practice')
            </div>
        </div>
    </div>
@endsection


