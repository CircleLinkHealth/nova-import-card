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
        <h3 align="center">Edit/Delete Observations for a User</h3>
    </div>
    <div class="container">
        <div class="col-md-6">
            <div class="input-group">
                <div>
                    <form action="{{route('observations-dashboard.list')}}" method="GET">
                        <div class="form-group">
                            <p>Insert User Id:</p>
                            <input type="number" name="userId"required>
                        </div>
                        <div>
                            <input align="center" type="submit" value="Submit" class="btn btn-info">
                        </div>
                        @if (session('msg'))
                            <div class="alert alert-success">
                                {{ session('msg') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @isset($user)
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                Observations List for: {{$user->display_name}}.
            </div>
            <div class="panel-body">
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <th>Observation Key</th>
                        <th>Value</th>
                        <th>Method</th>
                        <th>Message Id</th>
                        <th>Practice Id</th>
                        <th>Date</th>
                        <th> </th>
                        <th> </th>
                    </tr>
                    @foreach($observations as $o)
                        <tr>
                            <td>{{$o->obs_key}}</td>
                            <td>{{$o->obs_value}}</td>
                            <td>{{$o->obs_method}}</td>
                            <td>{{$o->obs_message_id}}</td>
                            <td>{{$o->program_id}}</td>
                            <td>{{$o->obs_date}}</td>
                            <td><form action="{{route('observations-dashboard.edit')}}" method="GET">
                                                <input type="hidden" name="obsId" value="{{$o->id}}">
                                                <input align="center" type="submit" value="Edit" class="btn btn-warning">
                                                <br>
                                            </form>
                                        </td>
                            <td><form action="" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="fromDate" value="">
                                    <input type="hidden" name="toDate" value="">
                                    <input type="hidden" name="status" value="">
                                    <input type="hidden" name="practice_id" value="">
                                    <input align="center" type="submit" value="Delete" class="btn btn-danger">
                                    <br>
                                </form>
                            </td>
                        </tr>

                    @endforeach

                </table>

                {{--{!! $observations->appends(Input::except('page'))->links() !!}--}}
            </div>
        </div>
    </div>
    @endisset
@endsection