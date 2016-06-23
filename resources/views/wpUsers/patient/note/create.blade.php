@extends('partials.providerUI')

@section('title', 'Patient Note Creation')
@section('activity', 'Patient Note Creation')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>



    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Record New Note
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.store', ['patientId' => $patient]), 'class' => 'form-horizontal')) !!}

                @include('partials.userheader')

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Note Topic
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="activityKey" name="type"
                                                    class="selectpickerX dropdownValid form-control"
                                                    data-size="10" required>
                                                <option value=""> Select Topic</option>
                                                @foreach ($note_types as $note_type)
                                                    <option value="{{$note_type}}"> {{$note_type}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="observationDate">
                                            When (Patient Local Time):
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <input name="performed_at" type="datetime-local"
                                                   class="selectpickerX form-control"
                                                   data-width="95px" data-size="10" list max="{{$userTime}}"
                                                   value="{{$userTime}}"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label for="activityKey">
                                            Performed By
                                        </label>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <select id="author_id" name="author_id"
                                                    class="selectpickerX dropdown Valid form-control" data-size="10" required disabled>
                                                    <option value="{{$author_id}}" selected> {{$author_name}} </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-block col-md-6">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="panel-group" id="accordion">
                                            <label data-toggle="collapse" data-target="#collapseOne">
                                                <div class="radio"><input type="checkbox" name="meta[0][meta_key]"
                                                                          id="phone"
                                                                          value="phone"/><label
                                                            for="phone"><span> </span>Patient Phone Session</label>
                                                </div>
                                            </label>

                                            <div id="collapseOne" class="panel-collapse collapse in">
                                                <div class="radio-inline"><input type="radio"
                                                                                 name="phone"
                                                                                 value="inbound" id="Inbound"/><label
                                                            for="Inbound"><span> </span>Inbound</label>
                                                </div>
                                                <div class="radio-inline"><input type="radio"
                                                                                 name="phone"
                                                                                 value="outbound" id="Outbound"/><label
                                                            for="Outbound"><span> </span>Outbound</label></div>
                                                <input type="hidden" name="call_status" value="">
                                                <div><div class="radio-inline"><input type="checkbox"
                                                                                 name="call_status"
                                                                                 value="reached" id="reached"/><label
                                                            for="reached"><span> </span>Successful Clinical Call</label>
                                                </div></div>
                                                <input type="hidden" name="tcm" value="hospital">
                                                <div><div class="radio-inline"><input type="checkbox"
                                                                                      name="tcm"
                                                                                      value="true" id="true"/><label
                                                                for="true"><span> </span>Patient in Hospital/ER (now or recently)</label>
                                                    </div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-block col-md-12">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="hidden" name="body" value="body">
                                        <textarea id="note" class="form-control" rows="10" cols="100" placeholder="Enter Note..."
                                                  name="body" required></textarea> <br/>
                                    </div>
                                </div>
                                <div class="form-block col-md-6">
                                    <div class="row">
                                        <div class="new-note-item">
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <label for="activityKey">
                                                        Send Note To:
                                                    </label>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <select id=performedBy" name=careteam[]"
                                                                class="selectpicker dropdown Valid form-control"
                                                                data-size="10"
                                                                multiple>
                                                            @foreach ($careteam_info as $id => $name)
                                                                <option value="{{$id}}"> {{$name}} </option>
                                                            @endforeach
                                                                <option value="948">
                                                                    Patient Support
                                                                </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input type="hidden" name="patient_id" value="{{$patient->ID}}">
                                    <input type="hidden" name="logger_id" value="{{Auth::user()->ID}}">
                                    <input type="hidden" name="author_id" value="{{Auth::user()->ID}}">
                                    <input type="hidden" name="patientID" id="patientID" value="{{$patient->ID}}">
                                    <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                </div>
                                <div class="form-item form-item-spacing text-center">
                                    <div>
                                        <div class="col-sm-12">
                                            <input type="hidden" value="new_activity"/>
                                            <button id="update" name="submitAction" type="submit"
                                                    value="new_activity"
                                                    class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                Save/Send Note
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    $('.collapse').collapse();

                                    $("input:checkbox").on('click', function () {
                                        // in the handler, 'this' refers to the box clicked on
                                        var $box = $(this);
                                        if ($box.is(":checked")) {
                                            // the name of the box is retrieved using the .attr() method
                                            // as it is assumed and expected to be immutable
                                            var group = "input:checkbox[name='" + $box.attr("name") + "']";
                                            // the checked state of the group/box on the other hand will change
                                            // and the current value is retrieved using .prop() method
                                            $(group).prop("checked", false);
                                            $box.prop("checked", true);
                                        } else {
                                            $box.prop("checked", false);
                                        }
                                    });
                                </script>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
@endsection