@extends('app')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1 well well">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Quick Add Patient</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <hr />
                {!! Form::open(array('url' => '#', 'class' => 'form-horizontal')) !!}
                <div class="form-group">
                    <div class="row">
                        <div class="col-xs-1">{!! Form::label('first_name', 'First Name:') !!}</div>
                        <div class="col-xs-3">{!! Form::text('first_name', '', ['class' => 'form-control', 'required']) !!}</div>
                        <div class="col-xs-1">{!! Form::label('last_name', 'Last Name:') !!}</div>
                        <div class="col-xs-3">{!! Form::text('last_name', '', ['class' => 'form-control', 'required']) !!}</div>
                        <div class="col-xs-1">{!! Form::label('email', 'Email Address:') !!}</div>
                        <div class="col-xs-3">{!!Form::input('email', 'email', null, ['class' => 'form-control', 'required'])!!}</div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">

                        <div class="col-xs-1">{!! Form::label('mail', 'Mailing Address:') !!}</div>
                        <div class="col-xs-3">{!! Form::text('description', '', ['class' => 'form-control', 'required']) !!}</div>
                        <div class="col-xs-1">{!! Form::label('mrn', 'MRN#:') !!}</div>
                        <div class="col-xs-3">{!! Form::text('mrn', '', ['class' => 'form-control', 'required']) !!}</div>
                        <div class="col-xs-1">{!! Form::label('CCM', 'CCM Enrollment') !!}</div>
                        <div class="col-xs-1">{!! Form::radio('CCM', 'enrolled', true) !!} Enrolled</div>
                        <div class="col-xs-2">{!! Form::radio('CCM', 'unenrolled') !!} Un-enrolled</div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-xs-1">{!! Form::label('date', 'Date Of Birth:') !!}</div>
                        <div class="col-xs-3">{!!Form::input('date', 'date', null, ['class' => 'form-control', 'placeholder' => 'Date', 'required'])!!}</div>
                        <div class="col-xs-1">{!! Form::label('mrn', 'MRN#:') !!}</div>
                        <div class="col-xs-3">{!! Form::text('mrn', '', ['class' => 'form-control', 'required']) !!}</div>

                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-xs-1">{!! Form::label('date', 'Preferred Contact Days:') !!}</div>
                        <div class="col-xs-4">
                            {!! Form::select('contact_days[]', $days , null, ['class' => 'selectpicker', 'multiple', 'data-size' => '10']) !!}
                        </div>
                    </div>
                </div>

            {!! Form::submit('Save') !!}
        </div>
    </div>


    {{--@foreach( $headings as $heading )--}}
    {{--{{ $heading }} <br />--}}
    {{--@endforeach--}}

@stop