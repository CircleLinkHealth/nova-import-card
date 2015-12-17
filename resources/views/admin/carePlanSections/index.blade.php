@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Care Plans</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.careplansections.create', array()) }}" class="btn btn-success">New User Care Plan</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Care Plans</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.careplansections.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        </div>

                        <h2>Filter</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('filterUser', array('all' => 'All Users', '' => 'No User') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                            </div>
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
                                    <td><a href="{{ URL::route('admin.careplansections.show', array('id' => $careplan->id)) }}" class="btn btn-primary">Detail</a></td>
                                    <td>{{ $careplan->name }}</td>
                                    <td>{{ $careplan->type }}</td>
                                    <td>{{ $careplan->user_id }}</td>
                                    <td><a href="{{ URL::route('admin.careplansections.edit', array('id' => $careplan->id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.careplansections.destroy', array('id' => $careplan->id)) }}" class="btn btn-warning">Remove</a></td>
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
