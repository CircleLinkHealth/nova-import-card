@extends('cpm-admin::partials.adminUI')

@section('content')
    <div class="container">
        <div class="row">
            @if(Session::has('message'))
                <div class="col-md-12">
                    @if(Session::get('type') == 'success')
                        <div class="alert alert-success">
                            {!!Session::get('message')!!}
                        </div>
                    @elseif(Session::get('type') == 'error')
                        <div class="alert alert-danger">
                            {!!Session::get('message')!!}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {!!Session::get('message')!!}
                        </div>
                    @endif
                </div>
            @endif

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Import patients from Batch {{$batch->id}} for
                        practice {{$practice->display_name}}</div>

                    <div class="panel-body">
                        <form method="POST" action="{{ route('admin.enrollees.import', [$batch->id]) }}">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        @foreach($enrollees as $enrollee)
                                            <div class="col-xs-12" style="padding-bottom: 3px;">
                                                <span>import? </span>
                                                <input id="enrollee_ids" name="enrollee_id[]" type="checkbox"
                                                       value="{{$enrollee->id}}" {{empty($enrollee->user_id) ?'':'disabled'}} {{app(\CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService::class)->isCcda($enrollee->medical_record_type) ?'':'disabled'}} {{old('enrollee_id') == $enrollee->id ?'selected':''}} >
                                                {!! empty($enrollee->user_id) ?'': '<u>Patient already has a careplan.</u>  ' !!} {{app(\CircleLinkHealth\Eligibility\MedicalRecordImporter\ImportService::class)->isCcda($enrollee->medical_record_type) ?'':'Cannot import'}}
                                                <b>{{ $enrollee->first_name }} {{ $enrollee->last_name }}</b>
                                                , {{ $enrollee->dob->toDateString() }}, {{ $enrollee->mrn }}
                                                </input>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="form-group">
                                        <input class="btn btn-success" type="submit" value="Import">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection