@extends('partials.providerUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/select.js') }}"></script>
    <div class="row" style="margin-top:60px;">
        <div class="row" style="margin:20px 0px 40px 0px;">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    {!! Form::open(array('url' => URL::route('patients.select.process', array()), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                </div>
                <div class="row">
                    <div class="col-xs-4 text-right">{!! Form::label('findUser', 'Find User:') !!}</div>
                    <div class="col-xs-8">{!! Form::select('findUser', $patients, '', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                </div>
                <div class="row">
                    <div class="col-xs-12 text-center" style="margin:20px 0px;">
                        {!! Form::submit('View patient', array('class' => 'btn btn-success')) !!}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop