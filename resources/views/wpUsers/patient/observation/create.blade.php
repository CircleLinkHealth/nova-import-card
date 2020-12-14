@extends('core::partials.providerUI')

@section('title', 'Input Observations')
@section('activity', 'Input Observations')

@section('content')

    @push('scripts')
        <script>
            $(function () {
                $(".observation").select2();
            });
            $('#observationDate').datetimepicker({
                format: 'Y-m-d H:i',
                step: 1
            });
        </script>
    @endpush

    <div class="row" style="margin:60px 0px;">
        <div class="col-lg-10 col-lg-offset-1">
            @include('core::partials.errors.errors')
        </div>
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    New Observation
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    {!! Form::open(['url' => route('patient.observation.store', ['patientId' => $patient->id]), 'class' => 'form-horizontal']) !!}
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="col-sm-12">
                                    <label for="observationType">
                                        Observation Type:
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <select id="observationType" name="observationType" class="observation selectpickerX dropdownValid form-control" data-size="10" required style="width: 100%;">
                                        <option value="">Select an Observation</option>
                                        @foreach($observationCatecories as $category)
                                            <optgroup label="{{ $category['display_name']}}">
                                                @foreach($acceptedObservationTypes->where('category_name', $category['name']) as $key => $acceptedObservation)
                                                    <option value="{{$key}}" {{old('observationType') === $key ? 'selected' : ''}}>{{$acceptedObservation['display_name']}}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="col-sm-12">
                                    <label for="observationDate">
                                        Observation Date and Time (in EST Timezone):
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input id="observationDate" name="observationDate" type="text" class="selectpickerX form-control" value="{{ (old('observationDate') ? old('observationDate') : date('Y-m-d H:i:s')) }}" data-field="datetime" data-format="yyyy-MM-dd HH:mm" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <div class="col-sm-12">
                                    <label for="observationSource">
                                        Source of Observation:
                                    </label>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <select id="observationSource" name="observationSource" class="selectpickerX dropdownValid form-control" data-size="10"  required>
                                            <option value="ov_reading" {{old('observationSource') === 'ov_reading' ? 'selected' : ''}}>Office Visit (OV) reading</option>
                                            <option value="lab" {{old('observationSource') === 'lab' ? 'selected' : ''}}>Lab Test</option>
                                            <option value="manual_input" {{in_array(old('observationSource'), ['manual_input', '']) ? 'selected' : ''}}>Patient Reported</option>
                                            <option value="device" {{ old('observationSource') === 'device' ? 'selected' : ''}}>Device</option>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group col-md-6">
                                <div class="col-sm-12">
                                    <label for="observationValue">
                                        Value:
                                    </label>
                                </div>
                                <div class="form-group col-sm-6">
                                    <input type="text" class="form-control" name="observationValue" id="observationValue" placeholder="Enter Data" value="{{ (old('observationValue') ? old('observationValue') : '') }}" required>
                                </div>
                            </div>
                        </div>

                    <div class="row" style="margin:30px 0px;">
                        <div class="col-lg-12">
                            <div class="text-center" style="margin-right:20px;">
                                <input type="hidden" name="patientId" id="patientId" value="{{ $patient->id }}">
                                <input type="hidden" name="userId" id="userId" value="{{ $patient->id }}">
                                <input type="hidden" name="programId" id="programId" value="{{ $patient->program_id }}">

                                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@stop
