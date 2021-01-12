@extends('partials.adminUI')

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
                    <div class="panel-heading">Import patients who have consented</div>

                    <div class="panel-body">
                        <form method="POST" action="{{ route('admin.enrollees.import.from.all.practices') }}">
                            {!! csrf_field() !!}
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <select class="select2" name="enrollee_id">
                                            <option disabled {{old('enrollee_id') ?'':'selected'}}>search patients</option>
                                            @foreach($enrollees as $enrollee)
                                                <option value="{{$enrollee->id}}" {{empty($enrollee->user_id) ?'':'disabled'}} {{app(CircleLinkHealth\Eligibility\ProcessEligibilityService::class)->isCcda($enrollee->medical_record_type) ?'':'disabled'}} {{old('enrollee_id') == $enrollee->id ?'selected':''}} >
                                                    {{empty($enrollee->user_id) ?'': 'CAREPLAN EXISTS!!  '}} {{app(CircleLinkHealth\Eligibility\ProcessEligibilityService::class)->isCcda($enrollee->medical_record_type) ?'':'NO CCDA FOUND'}} {{ $enrollee->first_name }} {{ $enrollee->last_name }}
                                                    , {{ $enrollee->dob->toDateString() }}, {{ $enrollee->mrn }}
                                                    ({{ $practices[$enrollee->practice_id]->display_name }})
                                                </option>
                                            @endforeach
                                        </select>
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

@push('scripts')

@endpush