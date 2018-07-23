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

    @push('styles')
        <style>
            .edit_button {
                -webkit-appearance: none;
                outline: none;
                border: 0;
                background: transparent;
            }
        </style>
    @endpush

    @include('partials.confirm-modal')

    <form id="newNote" method="post" action="{{route('patient.note.store', ['patientId' => $patient->id])}}"
          class="form-horizontal">
        <div class="row" style="margin-top:30px;">
            <div class="main-form-container col-lg-6 col-lg-offset-3 col-md-10 col-md-offset-1"
                 style="border-bottom: 3px solid #50b2e2;">
                <div class="row">
                    <div class="main-form-title col-lg-12"> Record New Note</div>


                    {{ csrf_field() }}

                    @include('partials.userheader')

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                         style=" border-bottom:3px solid #50b2e2;padding: 8px 0px;">

                        <div class="col-xs-12" style="">
                            <div class="col-xs-8"><input type="text" class="form-control" name="general_comment"
                                                         id="general_comment"
                                                         value="{{$patient->patientInfo->general_comment}}"
                                                         placeholder="{{$patient->patientInfo->general_comment == '' ? 'Enter General Comment...' : $patient->patientInfo->general_comment}}"
                                                         aria-describedby="sizing-addon2"
                                                         style="margin: 0 auto; text-align: left; color: #333;">
                            </div>
                            <div class="col-sm-4 pull-right"
                                 style="text-align: right;top: 9px;font-size: 22px;color: #ec683e;">
                                @include('partials.complex-ccm-badge')
                            </div>
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
                                            <label for="performed_at">
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

                        <!-- Phone Sessions -->
                        <div class="form-block col-md-12">
                            <div class="row">
                                <div class="new-note-item">
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <div class="panel-group" id="accordion">

                                                <label>
                                                    <div class="radio"><input type="checkbox" name="meta[0][meta_key]"
                                                                              id="phone"
                                                                              value="phone"/><label
                                                                for="phone"><span> </span>Patient Phone Session</label>
                                                    </div>
                                                </label>

                                                <div id="collapseOne" class="panel-collapse collapse in"
                                                     style="display: none;">
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
                                                            <div class="radio-inline">
                                                                <input type="radio"
                                                                       name="call_status"
                                                                       value="not reached"
                                                                       id="not-reached"/>
                                                                <label for="not-reached">
                                                                    <span> </span>Patient Not Reached
                                                                </label>
                                                            </div>
                                                            <div class="radio-inline">
                                                                <input type="radio"
                                                                       name="call_status"
                                                                       value="reached"
                                                                       id="reached"/>
                                                                <label for="reached">
                                                                    <span> </span>Successful Clinical Call
                                                                </label>
                                                            </div>
                                                            <!-- CPM-165 Ability for RN to mark unsuccessful call but NOT count towards an attempt -->
                                                            <div class="radio-inline">
                                                                <input type="radio"
                                                                       name="call_status"
                                                                       value="ignored"
                                                                       id="ignored"/>
                                                                <label for="ignored">
                                                                    <span> </span>Patient Busy - Rescheduled Call
                                                                </label>
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
                                                    <div>
                                                        <div class="radio-inline"><input type="checkbox"
                                                                                         name="medication_recon"
                                                                                         value="true"
                                                                                         id="medication_recon"/><label
                                                                    for="medication_recon"><span> </span>Medication
                                                                Reconciliation
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="tcm" value="hospital">
                                                    <div>
                                                        <div class="radio-inline"><input type="checkbox"
                                                                                         name="tcm"
                                                                                         value="true" id="tcm"/><label
                                                                    for="tcm"><span> </span>Patient in Hospital/ER (now
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
                                            <persistent-textarea storage-key="notes:{{$patient->id}}:add" id="note"
                                                                 class-name="form-control" :rows="10" :cols="100"
                                                                 placeholder="Enter Note..."
                                                                 name="body" :required="true"></persistent-textarea>
                                            <br>
                                        </div>
                                    </div>

                                    <!-- Enter CareTeam -->
                                    <div class="form-block col-md-12">
                                        <div class="row">
                                            <div class="new-note-item">
                                                @include('partials.sendToCareTeam')
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden Fields -->
                                    <div class="form-group col-sm-4">
                                        <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                        <input type="hidden" name="logger_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="author_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                    </div>

                                    <!-- Submit -->
                                    <div class="form-block form-item-spacing text-center">
                                        <div>
                                            <div class="col-sm-12">
                                                <button name="Submit" id="Submit" type="submit" value="Submit"
                                                        form="newNote"
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
                </div>
            </div>
        </div>
    </form>


    <div>
        <br/>
    </div>

    @push('scripts')
        <script>

            const userIsCCMCountable = @json(auth()->user()->isCCMCountable());

            $(document).ready(function () {

                function phoneSessionChange(e) {
                    if (e) {
                        if (e.currentTarget.checked) {
                            $('#collapseOne').show();
                            $("#Inbound").prop("checked", false);
                            $("#Outbound").prop("checked", true);
                        }
                        else {
                            $('#collapseOne').hide();
                            $("#Inbound").prop("checked", false);
                            $("#Outbound").prop("checked", false);
                        }

                        if (window['App']) {
                            App.$emit('create-note:with-call', e.currentTarget.checked);
                        }
                    }
                    else {
                        $('#collapseOne').toggle();
                    }
                    //bug fix - this set value to phone="Outbound" in the form without
                    //the user knowing
                    //instead, set default only when visible
                    // $("#Outbound").prop("checked", true);
                }

                $('#phone').change(phoneSessionChange);

                phoneSessionChange({
                    currentTarget: {
                        checked: $('#phone').is(':checked')
                    }
                });

                function tcmChange(e) {
                    if (e) {
                        if (e.currentTarget.checked) {
                            $('#notify-careteam').prop("checked", true);
                            $('#notify-careteam').prop("disabled", true);

                            @empty($notifies_text)
                            $('#who-is-notified').text("{{$patient->billingProviderUser()->fullName}}");
                            @endempty
                        }
                        else {
                            $('#notify-careteam').prop("checked", false);
                            $('#notify-careteam').prop("disabled", false);

                            $('#who-is-notified').text("{{$notifies_text}}");
                        }
                    }
                    else {

                    }
                }

                $('#tcm').change(tcmChange);

                tcmChange({
                    currentTarget: {
                        checked: $('#tcm').is(':checked')
                    }
                })
            });

            $('#newNote').submit(function (e) {
                e.preventDefault();
                const form = this;

                if (userIsCCMCountable && $('#phone').is(':checked') && (!form['call_status'] || !form['call_status'].value || !form['call_status'].value.length)) {
                    alert('Please select whether patient was reached or not.');
                    return;
                }

                $.get('/api/test').always(function (response) {
                    if (response.status == 200 || response.message == 'clh') {
                        var key = 'notes:{{$patient->id}}:add'
                        window.sessionStorage.removeItem(key)
                    }
                    form.submit()
                })

            })
        </script>
    @endpush
@endsection