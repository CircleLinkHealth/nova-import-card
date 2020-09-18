@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {{--  <script src="{{ mix('/js/bootstrap-select.min.js') }}"></script>  --}}

    @push('scripts')
        <script src="{{ mix('/js/typeahead.bundle.js') }}"></script>
    @endpush
    
    <div class="container-fluid">
        {!! Form::open(array('url' => route('admin.families.store', array()), 'class' => 'form-horizontal')) !!}
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
                        @if(session('message'))
                        <div>
                            <h3>{{session('message')}}</h3>
                        </div>
                        @endif
                        <div>
                            <div class="form-group">
                                <div class="col-sm-2">{!! Form::label('family_member_ids', 'Enter Family User IDs: eg:(144,233,377,610,987)') !!}</div>
                                <div class="col-sm-10">{!! Form::text('family_member_ids', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="pull-right">
                            <div class="pull-right">
                                {{--<a href="{{ route('admin.families.index', array()) }}" class="btn btn-danger">Cancel</a>--}}
                                {!! Form::submit('Create Family', array('class' => 'btn btn-success')) !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        {!! Form::close() !!}
    </div>
    </div>
@stop

