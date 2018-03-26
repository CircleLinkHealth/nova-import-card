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
        <div class="btn-group btn-group-justified" role="group" aria-label="...">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default">Daily</button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default">Lost/Added Patients</button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default">Added/Withdrawn/Paused List</button>
            </div>
        </div>
        <div class="form-group">
            <article>Active Patients as of 11pm ET on:</article>
            <input id="date" type="date" name="date" value="{{$date->toDateString()}}"required class="form-control">
            <input type="submit" value="Submit" class="btn btn-info">
        </div>
        <div class="col-md-4">
            <div>
                <span>Hours Behind : </span>
                <span class="label label-info">{{$hoursBehind}}</span>
            </div>
        </div>

    </div>
@endsection


