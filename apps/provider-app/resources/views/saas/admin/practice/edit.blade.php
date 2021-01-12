@extends('partials.providerUI')

@section('title', 'Edit Practice')
@section('activity', 'Edit Practice')

@section('content')
    <style>
        .form-group {
            margin: 20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">

                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Editing Practice: {{ $program->display_name }}
                    </div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')


                        {!! Form::open(array('url' => route('saas-admin.practices.update', array('id' => $program->id)), 'class' => 'form-horizontal')) !!}

                        <div class="form-group">

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('display_name', $program->display_name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('clh_pppm', 'CPM Price') !!}</div>
                                <div class="col-xs-3"><input class="form-control" name="clh_pppm" style="width: 100%"
                                                             @if(isset($program->clh_pppm)) value="{{$program->clh_pppm}}" @endif/>
                                </div>
                                <div class="col-xs-1">{!! Form::label('term_days', 'Terms (days)') !!}</div>
                                <div class="col-xs-3"><input class="form-control" name="term_days" style="width: 100%"
                                                             @if(isset($program->term_days)) value="{{$program->term_days}}" @endif/>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('active', 'Active') !!}</div>
                                <div class="col-xs-10">
                                    <input id="active" name="active" checked type="checkbox" class="form-control">
                                    <label for="active"><span> </span></label>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">
                                    <label class="control-label" for="primary_location">
                                        Select Primary Location<br></label>
                                </div>
                                <div class="col-xs-8">
                                    <select id="primary_location" name="primary_location"
                                            class="primary_location dropdown Valid form-control" required>
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}"
                                                    @if($location->is_primary) selected @endif>{{$location->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ route('saas-admin.practices.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Practice', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop