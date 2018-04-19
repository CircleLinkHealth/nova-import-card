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
        <h3 align="center">Edit Call Status</h3>
        <hr><br>
        <div class="text-center">
            <div>
                <h4 class="ops-dboard-title">Note Info</h4>
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <td>Note ID:</td>
                        <td>{{$note->id}}</td>
                    </tr>
                    <tr>
                        <td>Note type:</td>
                        <td>{{$note->type}}</td>
                    </tr>
                    <td>Date Performed:</td>
                    <td>{{$note->performed_at}}</td>
                </table>
            </div>

        </div>
    </div>
@endsection