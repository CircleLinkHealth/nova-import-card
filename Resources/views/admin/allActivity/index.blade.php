@extends('cpm-admin::partials.adminUI')

@section('content')

    @push('styles')
        <style>

            table {
                table-layout: fixed;
            }

            td {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
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

            th#is-phi {
                width: 5%;
            }

            th#old-value {
                width: 25%;
            }

            th#new-value {
                width: 25%;
            }

            th#date {
                width: 11%;
            }

            th#ip-address {
                width: 9%;
            }

            .center-align {
                text-align: center;
            }


        </style>
    @endpush
    <div class="container-fluid">
        <form action="{{$submitUrl ?? route('revisions.all.activity')}}" method="GET">
            <div class="row">
                <div class="col-md-3 col-md-offset-2">
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
                <div class="col-md-2">
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
                    <div class="col-md-4 col-md-offset-4 alert alert-danger">
                        @include('provider.partials.errors.validation')
                    </div>
                </div>
            </div>
        @endif

        <br/>

        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{isset($user) ? "PHI History for {$user->getFullName()}" : 'History'}}
                </div>
                <div class="panel-body">
                    @if($revisions->isNotEmpty())

                        <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                            <thead>
                            <tr>
                                <th id="change-id">Change Id</th>
                                <th id="change-id">Changed By</th>
                                <th id="type">Type</th>
                                <th id="key">Key</th>
                                <th id="is-phi">Is PHI</th>
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
                                    <th>{{$history->user_id ? link_to_route('admin.users.edit', $history->user_id, [$history->user_id]) : 'System'}}</th>
                                    <td title="{{$history->revisionable_type}}">{{str_replace('App\\', '',$history->revisionable_type)}}</td>
                                    <td title="{{$history->key}}">{{$history->key}}</td>
                                    <td>{{$history->is_phi ? 'Yes' : 'No'}}</td>
                                    <td title="{{$history->old_value}}">{{$history->old_value}}</td>
                                    <td title="{{$history->new_value}}">{{$history->new_value}}</td>
                                    <td>{{$history->updated_at}}</td>
                                    <td>{{$history->ip}}</td>
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

                    @else
                        <h5>No data found.</h5>
                    @endif
                </div>
            </div>
        </div>

    </div>

@stop