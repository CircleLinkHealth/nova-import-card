@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    @push('scripts')
        <script type="text/javascript" src="{{ mix('/js/admin/observations.js') }}"></script>
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
                    <div class="col-sm-8">
                        <h1>Observations</h1>
                    </div>
                    @if(Cerberus::hasPermission('observation.create'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
{{--                                <a href="{{ route('observations.create') }}" class="btn btn-success" disabled="disabled">Input Observation</a>--}}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Observations</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            <div class="col-sm-12">
                                {!! Form::open(array('url' => route('admin.observations.index', array()), 'class' => 'form-horizontal')) !!}
                                <h3>Filter</h3>
                                <div class="row form-group">
                                    <div class="col-xs-2">{!! Form::label('filterUser', 'User:') !!}</div>
                                    <div class="col-xs-4">{!! Form::select('filterUser', array('all' => 'All Users') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    <div class="col-xs-2">{!! Form::label('filterObsKey', 'Obs Key:') !!}</div>
                                    <div class="col-xs-4">{!! Form::select('filterObsKey', array('all' => 'All') + $obsKeys, $filterObsKey, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                </div>
                                <div class="row form-group" style="margin-top:50px;">
                                    <div class="col-sm-12">
                                        <div class="" style="text-align:center;">
                                            {!! Form::hidden('action', 'filter') !!}
                                            {!! Form::submit('Apply Filters', array('class' => 'btn btn-orange')) !!}
                                            {!! Form::submit('Reset Filters', array('class' => 'btn btn-orange')) !!}
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
                                <td><strong>msg id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>sequence</strong></td>
                                <td><strong>obs_date</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>obs method</strong></td>
                                <td><strong>value</strong></td>
                                <td><strong>unit</strong></td>
                                <td><strong>program</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $observations as $observation )
                                <tr>
                                    <td><a href="{{ route('admin.observations.show', array('id' => $observation->id)) }}" class="btn btn-primary">{{ $observation->id }}</a></td>
                                    @if( isset($observation->question->qid) )
                                        <td><a href="{{ route('admin.questions.show', array('id' => $observation->question->qid)) }}" class="btn btn-orange btn-xs">{{ $observation->obs_message_id }}</a></td>
                                    @else
                                        <td>{{ $observation->obs_message_id }}</td>
                                    @endif
                                    <td><a href="{{ route('admin.users.edit', array('id' => $observation->user_id)) }}" class="btn btn-orange btn-xs">{{ $observation->user_id }}</a></td>
                                    <td>{{ $observation->sequence_id }}</td>
                                    <td>{{ $observation->obs_date }}</td>
                                    <td>{{ $observation->obs_key }}</td>
                                    <td>{{ $observation->obs_method }}</td>
                                    <td>{{ $observation->obs_value }}</td>
                                    <td>{{ $observation->obs_unit }}</td>
                                    <td>
                                        <a href="{{ route('provider.dashboard.manage.notifications', [$wpUser->primaryPractice->name]) }}"
                                           class="btn btn-orange btn-xs">{{ $observation->program_id }}</a></td>
                                    <td>
                                        @if(Cerberus::hasPermission('observation.update'))
                                            <a href="{{ route('admin.observations.edit', array('id' => $observation->id)) }}" class="btn btn-primary">Edit</a>
                                        @endif
                                        @if(Cerberus::hasPermission('observation.delete'))
                                            <a href="{{ route('admin.observations.destroy', array('id' => $observation->id)) }}" class="btn btn-warning">Remove</a></td>
                                        @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $observations->appends(['action' => 'filter', 'filterUser' => $filterUser])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
