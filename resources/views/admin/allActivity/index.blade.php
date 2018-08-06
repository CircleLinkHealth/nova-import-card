@extends('partials.adminUI')

@section('content')

    @push('styles')
        <style>

            table {
                table-layout: fixed;
            }

            td {
                word-break: break-all;
            }

            th#change-id {
                width: 5%;
            }

            th#type {
                width: 8%;
            }

            th#key {
                width: 12%;
            }

            th#old-value {
                width: 28%;
            }

            th#new-value {
                width: 28%;
            }

            th#date {
                width: 11%;
            }

            th#ip-address {
                width: 8%;
            }

            .center-align {
                text-align: center;
            }


        </style>
    @endpush
    <div class="container-fluid">

        <form action="{{route('all.activity')}}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date-from">From:</label>
                        <input id="date-from" type="date"
                               name="date-from" value="{{$startDate->toDateString()}}"
                               required class="form-control"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date-to">To:</label>
                        <input id="date-to" type="date"
                               name="date-to" value="{{$endDate->toDateString()}}"
                               required class="form-control"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div class="form-group">
                        <input type="submit" value="Submit" class="btn btn-info">
                    </div>
                </div>
            </div>
        </form>

        @if($errors->isNotEmpty())
            <div class="row">
                <div class="container">
                    <div class="col-md-4 alert alert-danger">
                        @include('provider.partials.errors.validation')
                    </div>
                </div>
            </div>
        @endif

        <br/>

        @if($revisions->isNotEmpty())
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                            <thead>
                            <tr>
                                <th id="change-id">Change Id</th>
                                <th id="type">Type</th>
                                <th id="key">Key</th>
                                <th id="old-value">Old Value</th>
                                <th id="new-value">New Value</th>
                                <th id="date">Date</th>
                                <th id="ip-address">IP Address</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($revisions as $history)
                                <tr>
                                    <th>{{$history->id}}</th>
                                    <td>{{$history->revisionable_type}}</td>
                                    <td>{{$history->key}}</td>
                                    <td>{{$history->old_value}}</td>
                                    <td>{{$history->new_value}}</td>
                                    <td>{{$history->updated_at}}</td>
                                    <td>{{$history->ip_address}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12 center-align">
                            {{$revisions->appends([
                                'date-from' => $startDate->toDateString(),
                                'date-to' => $endDate->toDateString()
                            ])->render()}}
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

@stop