@extends('partials.adminUI')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-2">
                        <h1>Care Plans</h1>
                    </div>
                    <div class="col-sm-10">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.careplans.create', array()) }}" class="btn btn-success">New Care Plan</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Care Plans</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            <div class="col-sm-12">
                                {!! Form::open(array('url' => URL::route('admin.careplans.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                                <h2>Filter</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('filterUser', array('all' => 'All Users', '' => 'No User') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:50px;">
                                    <div class="col-sm-12">
                                        <div class="" style="text-align:center;">
                                            {!! Form::hidden('action', 'filter') !!}
                                            {!! Form::submit('Apply Filters', array('class' => 'btn btn-orange')) !!}
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>name</strong></td>
                                <td><strong>type</strong></td>
                                <td><strong>user_id</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $careplans as $careplan )
                                <tr>
                                    <td><a href="{{ URL::route('admin.careplans.show', array('id' => $careplan->id)) }}" class="btn btn-primary btn-xs">Detail</a></td>
                                    <td>{{ $careplan->name }}</td>
                                    <td>{{ $careplan->type }}</td>
                                    <td>{{ $careplan->user_id }}</td>
                                    <td class="text-right"><a href="{{ URL::route('admin.careplans.edit', array('id' => $careplan->id)) }}" class="btn btn-xs btn-primary">Edit</a> <a href="{{ URL::route('admin.careplans.destroy', array('id' => $careplan->id)) }}" class="btn btn-xs btn-danger">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if (count($careplans) > 0)
                            {!! $careplans->appends(['action' => 'filter', 'filterUser' => $filterUser])->render() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
