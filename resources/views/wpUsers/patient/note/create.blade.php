@extends('partials.providerUI')

@section('title', 'Create Patient Note')
@section('activity', 'Patient Note Creation')

@section('content')

    <?php
    $userTime = \Carbon\Carbon::now();
    $userTime->setTimezone($userTimeZone);
    $userTimeGMT = \Carbon\Carbon::now()->setTimezone('GMT');
    $userTime = $userTime->format('Y-m-d\TH:i');
    $userTimeGMT = $userTimeGMT->format('Y-m-d\TH:i');
    ?>

    <style>
        .edit_button {
            -webkit-appearance: none;
            outline: none;
            border: 0;
            background: transparent;
        }
    </style>

    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1"
             style="border-bottom: 3px solid #50b2e2;">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Record New Note
                </div>


                <form method="post" action="{{URL::route('patient.note.store', ['patientId' => $patient])}}"
                      class="form-horizontal">

                    {{ csrf_field() }}

                    @include('partials.userheader')

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                         style=" border-bottom:3px solid #50b2e2;padding: 10px 48px;">

                        <div class="col-xs-12" style="">
                            <input type="text" class="form-control" name="general_comment" id="general_comment"
                                   value="{{$patient->patientInfo->general_comment}}"
                                   placeholder="{{$patient->patientInfo->general_comment == '' ? 'Enter General Comment...' : $patient->patientInfo->general_comment}}"
                                   aria-describedby="sizing-addon2"
                                   style="margin: 0 auto; text-align: left; color: #333;">
                        </div>
                    </div>

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                         style=" border:0px solid #50b2e2;padding: 10px 35px;">
                        <!-- Note Type -->
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

                        <!-- Performance Time -->
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

                        <!-- Author -->
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
                                                        class="selectpickerX dropdown Valid form-control" data-size="10"
                                                        required disabled>
                                                    <option value="{{$author_id}}" selected> {{$author_name}} </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Sessions -->
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
                                                                                     value="inbound"
                                                                                     id="Inbound"/><label
                                                                for="Inbound"><span> </span>Inbound</label>
                                                    </div>
                                                    <div class="radio-inline"><input type="radio"
                                                                                     name="phone"
                                                                                     value="outbound"
                                                                                     id="Outbound"/><label
                                                                for="Outbound"><span> </span>Outbound</label></div>

                                                    @if(auth()->user()->isCCMCountable())
                                                        <div>
                                                            <div class="radio-inline"><input type="checkbox"
                                                                                             name="call_status"
                                                                                             value="reached"
                                                                                             id="reached"/><label
                                                                        for="reached"><span> </span>Successful Clinical
                                                                    Call</label>
                                                            </div>
                                                        </div>

                                                    @else

                                                        <div>
                                                            <div class="radio-inline"><input type="checkbox"
                                                                                             name="welcome_call"
                                                                                             value="welcome_call"
                                                                                             id="welcome_call"/><label
                                                                        for="welcome_call"><span> </span>Successful
                                                                    Welcome Call</label>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="radio-inline"><input type="checkbox"
                                                                                             name="other_call"
                                                                                             value="other_call"
                                                                                             id="other_call"/><label
                                                                        for="other_call"><span> </span>Successful Other
                                                                    Patient Call</label>
                                                            </div>
                                                        </div>

                                                    @endif

                                                    <input type="hidden" name="tcm" value="hospital">
                                                    <div>
                                                        <div class="radio-inline"><input type="checkbox"
                                                                                         name="tcm"
                                                                                         value="true" id="true"/><label
                                                                    for="true"><span> </span>Patient in Hospital/ER (now
                                                                or
                                                                recently)</label>
                                                        </div>
                                                    </div>
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
                                    <!-- Enter Note -->
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <input type="hidden" name="body" value="body">
                                            <textarea id="note" class="form-control" rows="10" cols="100"
                                                      placeholder="Enter Note..."
                                                      name="body" required></textarea> <br/>
                                        </div>
                                    </div>

                                    <!-- Enter CareTeam -->
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
                                                            <select id=performedBy" name="careteam[]"
                                                                    class="selectpicker dropdown Valid form-control"
                                                                    data-size="10"
                                                                    multiple>
                                                                <!-- rework later, quick fix ticket: 679 !-->
                                                                @if($patient->program_id == 29)
                                                                    <option value="2584">Tina Booze</option>
                                                                @else
                                                                    @foreach ($careteam_info as $id => $name)
                                                                        <option value="{{$id}}"> {{$name}} </option>
                                                                    @endforeach
                                                                    <option value="948">Patient Support</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden Fields -->
                                    <div class="form-group col-sm-4">
                                        <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                        <input type="hidden" name="logger_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="author_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="patientID" id="patientID" value="{{$patient->id}}">
                                        <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                    </div>

                                    <!-- Submit -->
                                    <div class="form-block form-item-spacing text-center">
                                        <div>
                                            <div class="col-sm-12">
                                                <button name="Submit" id="Submit" type="submit" value="Submit"
                                                        class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                    Save/Send Note
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        // Script is for the "phone session" part

        $('.collapse').collapse();

        $("#phone").on('click', function () {
            // in the handler, 'this' refers to the box clicked on
            var $box = $(this);
            if ($box.is(":checked")) {
                // the name of the box is retrieved using the .attr() method
                // as it is assumed and expected to be immutable
                var group = "input:checkbox[name='" + $box.attr("name") + "']";
                // the checked state of the group/box on the other hand will change
                // and the current value is retrieved using .prop() method
                $(group).prop("checked", false);
                $("#Outbound").prop("checked", true);
                $box.prop("checked", true);

            } else {
                $("#Outbound").prop("checked", false);
                $box.prop("checked", false);
            }
        });

    </script>

@endsection