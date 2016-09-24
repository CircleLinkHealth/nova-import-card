@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <script src="{{ asset('/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('/js/typeahead.bundle.js') }}"></script>
    <div class="container-fluid">
        {!! Form::open(array('url' => URL::route('admin.families.store', array()), 'class' => 'form-horizontal')) !!}
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Families</h1>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Create Family</div>
                    <div class="panel-body">
                        <div class="input_fields_wrap">
                            <div class="form-group">
                                <div class="col-sm-2">{!! Form::label('family_member_ids', 'Enter Family User IDs: eg:(144,233,377,610,987)') !!}</div>
                                <div class="col-sm-10">{!! Form::text('family_member_ids', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="pull-right">
                            <div class="pull-right">
                                {{--<a href="{{ URL::route('admin.families.index', array()) }}" class="btn btn-danger">Cancel</a>--}}
                                {!! Form::submit('Create Family', array('class' => 'btn btn-success')) !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
@stop

