@extends('partials.providerUI')

@section('title', empty($note) ? 'Create Patient Note' : 'Edit Patient Note')
@section('activity', empty($note) ? 'Patient Note Creation' : 'Patient Note Edit')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
              integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
              crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

        <style>

            .modal-body {
                font-size: large;
            }

            .edit_button {
                -webkit-appearance: none;
                outline: none;
                border: 0;
                background: transparent;
            }

            .edgy-button {
                border-radius: 3px;
            }

            .radio-inline {
                padding-left: 0;
                margin-left: 0;
            }

            .multi-input-wrapper {
                margin-left: -10px;
                margin-bottom: 4px;
                padding-left: 6px;
                /*border: 1px solid #ccc;*/
                /*border-radius: 4px;*/
            }

            .phone-session-container, #tasks-container {
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .btn-grey {
                color: #fff;
                background-color: #868686;
                border-color: #5d5d5d;
            }

            .btn-grey:hover, .btn-grey:active, .btn-grey:focus {
                color: #fff;
                background-color: #5b5b5b;
                border-color: #353535;
            }

            .label-margin {
                margin-bottom: 9px;
            }

            body {
                font-family: 'Roboto', sans-serif !important;
            }

            b {
                font-weight: bolder;
            }
        </style>
    @endpush

    @include('partials.confirm-modal')

    @if (!empty($note) && $note->status === 'draft')
        <form id="delete-form" action="{{ route('patient.note.delete.draft', [
                                    'patientId' => $note->patient_id,
                                    'noteId' => $note->id
                                ]) }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    @endif

    <form id="newNote" method="post"
          action="{{route('patient.note.store', ['patientId' => $patient->id, 'noteId' => !empty($note) ? $note->id : null])}}"
          class="form-horizontal">
        <div class="row" style="margin-top:30px;">
            <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
                @include('errors.errors')
            </div>

            <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1"
                 style="border-bottom: 3px solid #50b2e2;">
                <div class="row">
                    <div class="main-form-title col-lg-12">
                        @if (empty($note) || $note->status === 'draft')
                            Record New Note
                        @else
                            Edit Note
                        @endif
                    </div>


                    {{ csrf_field() }}

                    @include('partials.userheader')

                    {{--If today is scheduled call day then just show banner--}}
                    @if(isset($patient)
                    && auth()->check()
                    && !isset($isPdf)
                    && auth()->user()->shouldShowBhiBannerIfPatientHasScheduledCallToday($patient))
                        @include('partials.providerUI.bhi-notification-banner', ['user' => $patient])
                    @endif

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 col-xs-12"
                         style=" border-bottom:3px solid #50b2e2;padding: 8px 0px;">

                        <div class="col-xs-12" style="">
                            <div class="col-lg-8 col-xs-4"><input type="text" class="form-control"
                                                                  name="general_comment"
                                                                  id="general_comment"
                                                                  value="{{$patient->patientInfo->general_comment}}"
                                                                  placeholder="Enter General Comment..."
                                                                  aria-describedby="sizing-addon2"
                                                                  style="margin: 0 auto; text-align: left; color: #333;">
                            </div>
                        </div>
                    </div>

                    @if (!empty($note) && $note->status === 'draft')
                        <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 col-xs-12"
                             style=" border-bottom:3px solid #50b2e2;padding: 8px 0px;">
                            <div class="col-md-12 text-center" style="line-height: 2.6;">
                                This is a draft note. Please finalize and click Save or
                                <a href="#" id="delete-note" style="font-weight: bold; color: red">
                                    DELETE HERE
                                </a>.
                            </div>
                            <br/>
                            <br/>
                        </div>
                    @endif

                    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 col-xs-12"
                         style=" border:0 solid #50b2e2;padding: 10px 35px;">

                        <div class="col-md-6" style="padding-top: 10px;">

                            <!-- Note Type -->
                            <div class="form-block col-md-12">
                                <div class="row">
                                    <div class="new-note-item">
                                        <div class="form-group" style="margin-bottom: 25px">
                                            <div class="col-sm-12">
                                                <label class="label-margin" for="activityKey">
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
                                                            <option @if (!empty($note) && $note->type === $note_type) selected
                                                                    @endif
                                                                    value="{{$note_type}}">
                                                                {{$note_type}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" style="padding-top: 10px">
                            <!-- Phone Sessions -->
                            <div class="form-block col-md-12">
                                <div class="row">
                                    <div class="new-note-item">
                                        <div class="form-group">
                                            <div class="col-sm-12 no-padding-left">
                                                <label id="phone-label" style="margin-bottom: 0">
                                                    <div>
                                                        <input type="checkbox"
                                                               @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                               @endif
                                                               id="phone"/>
                                                        <label for="phone" style="margin-bottom: 0">
                                                            <span> </span>Patient Phone Session
                                                        </label>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="col-sm-12 no-padding-left">
                                                <label id="task-label" style="display: none; margin-bottom: 0">
                                                    <div>
                                                        <input type="checkbox"
                                                               @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                               @endif
                                                               @if (!empty($call) && !empty($call->sub_type)) checked
                                                               @endif
                                                               id="task"/>
                                                        <label for="task" style="margin-bottom: 0">
                                                            <span> </span>Associate with Task
                                                        </label>
                                                    </div>
                                                </label>
                                            </div>
                                            @if(!empty($tasks))
                                                <div class="col-sm-12" id="tasks-container" style="display: none;">
                                                    <div class="multi-input-wrapper"
                                                         style="padding-bottom: 3px">
                                                        @foreach($tasks as $task)
                                                            <div class="radio">
                                                                <input type="radio"
                                                                       @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                       @endif
                                                                       @if (!empty($call) && $call->sub_type === $task->sub_type) checked
                                                                       @endif
                                                                       class="tasks-radio"
                                                                       name="task_id"
                                                                       value="{{$task->id}}"
                                                                       id="{{$task->id}}"/>
                                                                <label for="{{$task->id}}">
                                                                    <span> </span>{{$task->sub_type}}
                                                                    ; {{!empty($task->attempt_note) ? $task->attempt_note . ',' : ''}}
                                                                    due {{$task->window_end}}
                                                                    on {{$task->scheduled_date}}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        <!-- if editing a complete note and call is a task-->
                                            @if(!empty($call) && !empty($call->sub_type))
                                                <div class="col-sm-12" id="tasks-container" style="display: none;">
                                                    <div class="multi-input-wrapper"
                                                         style="padding-bottom: 3px">
                                                        <div class="radio">
                                                            <input type="radio"
                                                                   disabled
                                                                   class="tasks-radio"
                                                                   name="task_id"
                                                                   value="{{$call->id}}"
                                                                   id="{{$call->id}}"/>
                                                            <label for="{{$call->id}}">
                                                                <span> </span>{{$call->sub_type}}
                                                                ; {{!empty($call->attempt_note) ? $call->attempt_note . ',' : ''}}
                                                                due {{$call->window_end}}
                                                                on {{$call->scheduled_date}}
                                                            </label>
                                                        </div>

                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 call-status-radios phone-session-container"
                                                 style="padding: 0">
                                                <div class="col-sm-12">
                                                    <div class="panel-group" id="accordion" style="margin-bottom: 2px">
                                                        <div id="collapseOne" class="panel-collapse collapse in"
                                                             style="display: none;">
                                                            <div class="multi-input-wrapper">
                                                                <div class="radio-inline"><input type="radio"
                                                                                                 @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                                                 @endif
                                                                                                 @if (!empty($call) && !$call->is_cpm_outbound) checked
                                                                                                 @endif
                                                                                                 name="phone"
                                                                                                 value="inbound"
                                                                                                 class="phone-radios"
                                                                                                 id="Inbound"/><label
                                                                            for="Inbound"><span> </span>Inbound</label>
                                                                </div>
                                                                <div class="radio-inline"><input type="radio"
                                                                                                 @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                                                 @endif
                                                                                                 @if (!empty($call) && $call->is_cpm_outbound) checked
                                                                                                 @endif
                                                                                                 name="phone"
                                                                                                 class="phone-radios"
                                                                                                 value="outbound"
                                                                                                 id="Outbound"/><label
                                                                            for="Outbound"><span> </span>Outbound</label>
                                                                </div>
                                                                <hr style="margin-top: 0; margin-bottom: 0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    @if(auth()->user()->isCCMCountable())
                                                        <div class="call-status-radios multi-input-wrapper"
                                                             style="padding-bottom: 3px; display: none">
                                                            <div class="radio">
                                                                <input type="radio"
                                                                       @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                       @endif
                                                                       @if (!empty($call) && $call->status === \App\Call::NOT_REACHED) checked
                                                                       @endif
                                                                       class="call-status-radio"
                                                                       name="call_status"
                                                                       value="{{\App\Call::NOT_REACHED}}"
                                                                       id="not-reached"/>
                                                                <label for="not-reached">
                                                                    <span> </span>Patient Not Reached
                                                                </label>
                                                            </div>
                                                            <div class="radio">
                                                                <input type="radio"
                                                                       @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                       @endif
                                                                       @if (!empty($call) && $call->status === \App\Call::REACHED) checked
                                                                       @endif
                                                                       name="call_status"
                                                                       class="call-status-radio"
                                                                       value="{{\App\Call::REACHED}}"
                                                                       id="reached"/>
                                                                <label for="reached">
                                                                    <span> </span>Successful Clinical Call
                                                                </label>
                                                            </div>
                                                            <!-- CPM-165 Ability for RN to mark unsuccessful call but NOT count towards an attempt -->
                                                            <div class="radio">
                                                                <input type="radio"
                                                                       @if (!empty($note) && $note->status == \App\Note::STATUS_COMPLETE) disabled
                                                                       @endif
                                                                       @if (!empty($call) && $call->status === \App\Call::IGNORED) checked
                                                                       @endif
                                                                       name="call_status"
                                                                       class="call-status-radio"
                                                                       value="{{\App\Call::IGNORED}}"
                                                                       id="ignored"/>
                                                                <label for="ignored">
                                                                    <span> </span>Patient Busy - Rescheduled Call
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <hr style="margin-top: 0; margin-bottom: 0">
                                                    @endif
                                                    <div class="other-radios multi-input-wrapper"
                                                         style="padding-top: 3px; display: none">
                                                        <div><input type="checkbox"
                                                                    @if (!empty($note) && $note->did_medication_recon) checked
                                                                    @endif
                                                                    name="medication_recon"
                                                                    value="true"
                                                                    id="medication_recon"/>
                                                            <label for="medication_recon">
                                                                <span> </span>Medication Reconciliation
                                                            </label>
                                                        </div>
                                                        <input type="hidden" name="tcm" value="hospital">
                                                        <div><input type="checkbox"
                                                                    @if (!empty($note) && $note->isTCM) checked
                                                                    @endif
                                                                    name="tcm"
                                                                    value="true"
                                                                    id="tcm"/>
                                                            <label for="tcm">
                                                                <span> </span>Patient in Hospital/ER (now or
                                                                recently)
                                                            </label>
                                                        </div>
                                                        <div>
                                                            <input type="checkbox"
                                                                   name="success_story"
                                                                   value="true"
                                                                   id="success_story"/>
                                                            <label for="success_story">
                                                                <span> </span>Patient Success Story
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-12">

                            <!-- Send Note To: -->
                            <div class="form-block col-md-12 no-padding-right">
                                <div class="row">
                                    <div class="new-note-item">
                                        <div class="form-group" style="margin-bottom: 10px">
                                            <div class="col-sm-12 no-padding-right">
                                                <label>Send Note To:</label>
                                            </div>
                                            <div class="col-sm-12 no-padding-left no-padding-right"
                                                 style="padding-top: 8px">
                                                <div class="col-sm-4 no-padding-right">
                                                    <input type="checkbox" id="notify-circlelink-support"
                                                           name="notify_circlelink_support" value="1">
                                                    <label for="notify-circlelink-support"><span> </span>{{$patient->primaryPractice->saasAccountName()}}
                                                        Support</label>
                                                </div>
                                                <div class="col-sm-4 no-padding-right">
                                                    @empty($note_channels_text)
                                                        <b>This Practice has <em>Forwarded Note Notifications</em>
                                                            turned off. Please notify CirleLink support.</b>
                                                    @else
                                                        @empty($notifies_text)
                                                            <p style="color: red;">
                                                                No provider selected to receive email alerts. Use
                                                                the add ("+" sign) or edit (pencil) icons in
                                                                the
                                                                <strong>{{link_to_route('patient.careplan.print', '"Care Team"', ['patientId' => $patient->id])}}</strong>
                                                                section of
                                                                the {{link_to_route('patient.careplan.print', 'View CarePlan', ['patientId' => $patient->id])}}
                                                                page to
                                                                add or edit providers to receive email alerts.
                                                            </p>
                                                        @else

                                                            <input type="checkbox" id="notify-careteam"
                                                                   name="notify_careteam"
                                                                   @empty($note_channels_text) disabled="disabled"
                                                                   @endempty value="1">
                                                            <label id="notify-careteam-label" for="notify-careteam"
                                                                   style="display: inline-block;"><span
                                                                        id="notify-careteam-span"></span>Provider/CareTeam

                                                            </label>
                                                            <div class="label"
                                                                 data-tooltip="Notifies: {{ $notifies_text }} via {{ $note_channels_text }}">
                                                                <i class="fas fa-exclamation-circle fa-lg"
                                                                   style="color:#50b2e2"></i>
                                                            </div>
                                                        @endempty
                                                    @endempty

                                                </div>
                                                <div class="col-md-4">
                                                @if(authUserCanSendPatientEmail())
                                                    <!-- Email Patient Checkbox -->
                                                        <div class="form-block col-md-12" style="margin-bottom: 10px">
                                                            <input type="checkbox" id="email-patient"
                                                                   name="email-patient" value="1">
                                                            <label for="email-patient"><span> </span>Email
                                                                Patient</label>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="form-block col-md-12">
                            <div class="row col-md-12">

                                <!-- Compose Patient Mail: -->
                                <div class="new-note-item">
                                    <div class="form-group">
                                        <div id="email-patient-div" class="col-md-12" style="display: none;">
                                            <send-email-to-patient
                                                    patient-id="{{$patient->id}}" patient-email="{{$patient->email}}"
                                            ></send-email-to-patient>
                                        </div>
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

                                <div class="new-note-item">
                                    <!-- Enter Note -->
                                    <div class="form-group">
                                        @if($hasSuccessfulCall === false && !empty($patientRequestToKnow))
                                            @include('wpUsers.patient.note.patientRequestedYouToKnow')
                                        @endif
                                        <div class="col-sm-12">
                                            <i class="fas fa-book" style="font-size:12px; margin-right: 10px"></i>
                                            <label for="body">
                                                Full Note
                                            </label>
                                        </div>
                                        <div class="col-sm-12">
                                            <persistent-textarea ref="bodyComponent"
                                                                 storage-key="notes:{{$patient->id}}:add" id="note"
                                                                 class-name="form-control" :rows="10" :cols="100"
                                                                 placeholder="Write all your notes here to describe what happened in the call. This may contain observations, upcoming appointments, medication, and more."
                                                                 value="{{ !empty($note) ? $note->body : '' }}"
                                                                 name="body" :required="true"></persistent-textarea>
                                            <br>
                                            <span>Authored by: {{$author_name}}</span>
                                        </div>
                                    </div>

                                    <!-- Hidden Fields -->
                                    <div class="form-group col-sm-4">
                                        <input type="hidden" name="patient_id" id="patient_id" value="{{$patient->id}}">
                                        <input type="hidden" name="logger_id" id="logger_id"
                                               value="{{Auth::user()->id}}">
                                        <input type="hidden" name="author_id" value="{{Auth::user()->id}}">
                                        <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                        <input type="hidden" name="task_status" id="task_status" value="">
                                    </div>

                                    <!-- Submit -->
                                    <div class="form-block form-item-spacing text-right">
                                        <div>
                                            <div class="col-sm-12">
                                                <button name="Submit" id="Submit" type="submit" value="Submit"
                                                        form="newNote"
                                                        class="btn btn-primary btn-lg form-item--button form-item-spacing edgy-button">
                                                    Save Note
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

    <!-- Modal - CPM-182 -->
    <div class="modal fade" id="confirm-note-create" tabindex="-1" role="dialog"
         aria-labelledby="confirm-note-create-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="confirm-note-create-label">Successful Call?</h3>
                </div>
                <div class="modal-body">
                    <p>
                        We noticed you spent some time on this note, did you forget to click "Patient Phone Session" or
                        "Successful Clinical Call"?
                        <i class="far fa-smile"></i>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Yes</button>
                    <button id="confirm-note-submit" type="button" class="btn btn-grey">No</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm-task-completed" tabindex="-1" role="dialog"
         aria-labelledby="confirm-task-completed-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="confirm-note-create-label">Task Completed?</h3>
                </div>
                <div class="modal-body">
                    <p>
                        Is this task completed?
                    </p>
                </div>
                <div class="modal-footer">
                    <button id="confirm-task-completed-submit" type="button" class="btn btn-success">Yes</button>
                    <button id="confirm-task-not-completed-submit" type="button" class="btn btn-grey">No</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="saving-draft" tabindex="-1" role="dialog"
         aria-labelledby="saving-draft-label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="confirm-note-create-label">Care Plan needs approval</h3>
                </div>
                <div class="modal-body">
                    <p>
                        Before saving note, please ensure Care Plan is assessment-driven and ready for Dr. review,
                        then click "Ready for Dr.".
                    </p>
                </div>
                <div class="modal-footer text-center">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <a class="disabled btn btn-success" id="saving-draft-visit-careplan-link"
                               href="{{route('patient.careplan.print', ['patientId' => $patient->id])}}">
                                Visit Care Plan
                                <span id="saving-draft-loader" class="fa fa-spinner fa-spin"></span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button id="saving-draft-ok-button" type="button" class="btn btn-success">
                                Stay on this page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <br/>
    </div>

    @push('scripts')
        <script>

            var careplan = @json([
            'allCpmProblems' => $cpmProblems
            ]);
            const userIsCareCoach = @json(auth()->user()->isCareCoach());
            const userIsCCMCountable = @json(auth()->user()->isCCMCountable());
            const taskTypeToTopicMap = @json($task_types_to_topics);
            const noteTypesMap = @json($note_types);
            const patientNurseTasks = @json($tasks);
            const medications = @json($medications);
            const isEditingCompleteTask = @json(!empty($call) && !empty($call->sub_type));
            const editingTaskType = isEditingCompleteTask ? @json(optional($call)->sub_type) : undefined;
            const disableAutoSave = @json(!empty($note) && $note->status == \App\Note::STATUS_COMPLETE);
            const hasRnApprovedCarePlan = @json($hasRnApprovedCarePlan);

            const MEDICATIONS_SEPARATOR = '------------------------------';

            //flag to check whether form was submitted already
            //CPM-91 and CPM-437 double submitting notes
            let submitted = false;
            let form;
            let shouldValidateEmailBody = true;

            const waitForEl = function (selector, callback) {
                if (!$(selector).length) {
                    setTimeout(function () {
                        window.requestAnimationFrame(function () {
                            waitForEl(selector, callback)
                        });
                    }, 100);
                } else {
                    callback();
                }
            };

            function withApp(callback) {
                if (typeof App === 'undefined') {
                    setTimeout(() => withApp(callback), 500);
                    return;
                }
                callback(App);
            }

            let formAttachments;
            $(document).ready(function () {
                withApp(function (app) {
                    //Once nurse has attested call conditions add as form inputs and submit form
                    app.$on('call-conditions-attested', (data) => {
                        conditionsAttested = true;

                        let i = 0;
                        data.attested_problems.map(function (condition) {
                            $("<input>")
                                .attr("id", "attested_problems")
                                .attr("type", "hidden")
                                .attr("name", "attested_problems[" + i + "][ccd_problem_id]").val(condition).appendTo(form);
                            i++;
                        });
                        if (data.bypassed_bhi_validation) {
                            $("<input>")
                                .attr("id", "bypassed_bhi_validation")
                                .attr("type", "hidden")
                                .attr("name", "bypassed_bhi_validation").val('true').appendTo(form);
                        }

                        if (data.bypassed_all_validation) {
                            $("<input>")
                                .attr("id", "bypassed_all_validation")
                                .attr("type", "hidden")
                                .attr("name", "bypassed_all_validation").val('true').appendTo(form);
                        }

                        confirmSubmitForm();
                    });

                    let callIsSuccess = false;
                    let conditionsAttested = false;

                    if (medications && medications.length) {
                        waitForEl('#note', () => {
                            const noteBody = $('#note');
                            if (noteBody.val().length === 0) {
                                const medDescriptions = [];
                                for (let i = 0; i < medications.length; i++) {
                                    const med = medications[i];
                                    const desc = `-${med.name}\n\t${med.sig}`;
                                    medDescriptions.push(desc);
                                }

                                noteBody.val(`\n\n${MEDICATIONS_SEPARATOR}\n${medDescriptions.join('\n')}`);
                                const event = new Event('change');
                                document.getElementById('note').dispatchEvent(event);
                            }
                        });
                    }

                    app.$on('file-upload', (attachments) => {
                        formAttachments = attachments;
                    });

                    //CPM-182: Show a confirmation box if user spend time creating the note
                    //but did not register a phone session
                    const startDate = Date.now();

                    function phoneSessionChange(e) {
                        if (e) {
                            if (e.currentTarget.checked) {
                                $('#task-label').hide();
                                $('#collapseOne').show();
                                $('.call-status-radios').show();
                                $('.other-radios').show();
                                $("#Inbound").prop("checked", false);
                                $("#Outbound").prop("checked", true);
                            } else {
                                if (isEditingCompleteTask || patientNurseTasks.length) {
                                    $('#task-label').show();
                                }
                                $('#collapseOne').hide();
                                $('.call-status-radios').hide();
                                $('.other-radios').hide();
                                $("#Inbound").prop("checked", false);
                                $("#Outbound").prop("checked", false);
                            }

                            app.$emit('create-note:with-call', e.currentTarget.checked);

                        } else {
                            $('#collapseOne').toggle();
                            $('.call-status-radios').toggle();
                            $('.other-radios').toggle();
                        }
                        //bug fix - this set value to phone="Outbound" in the form without
                        //the user knowing
                        //instead, set default only when visible
                        // $("#Outbound").prop("checked", true);
                    }

                    let phoneEl = $('#phone');
                    phoneEl.change(phoneSessionChange);
                    //successful_clinical_call might be set but no $call. This can happen when we have draft notes
                    const hasClinicalCall = @json(!empty($note) && $note->successful_clinical_call == 1);
                    const hasPhoneSession = @json( (!empty($call) && empty($call->sub_type))) || hasClinicalCall;
                    phoneEl.prop('checked', hasPhoneSession);
                    phoneEl.trigger('change');

                    if (hasClinicalCall) {
                        $('#reached').prop('checked', true);
                    }

                    function associateWithTaskChange(e) {
                        if (!e) {
                            return;
                        }
                        if (e.currentTarget.checked) {

                            $('#phone-label').hide();
                            const selectList = $('#activityKey');
                            selectList.empty();
                            const defaultOption = new Option('Select Topic', "");
                            defaultOption.innerHTML = "Select Topic";
                            selectList.append(defaultOption);
                            for (let i in taskTypeToTopicMap) {
                                if (!taskTypeToTopicMap.hasOwnProperty(i)) {
                                    continue;
                                }
                                const o = new Option(taskTypeToTopicMap[i], taskTypeToTopicMap[i]);
                                o.innerHTML = taskTypeToTopicMap[i];
                                selectList.append(o);
                            }

                            //hide radios. will be decided what to show when a task is clicked
                            $('.call-status-radios').hide();
                            $('.other-radios').hide();

                            //if there is a task selected, select it as note topic
                            if ($('.tasks-radio').prop('checked')) {
                                $('.tasks-radio').trigger('change');
                            }

                            selectList.prop("disabled", true);
                            $('#tasks-container').show();

                            //if only one task, just select it
                            if (isEditingCompleteTask || patientNurseTasks.length === 1) {
                                $('.tasks-radio').prop('checked', true);
                                $('.tasks-radio').trigger('change');
                            }
                        } else {

                            $('.tasks-radio').prop('checked', false);
                            $('#phone-label').show();
                            const selectList = $('#activityKey');
                            selectList.empty();
                            const defaultOption = new Option('Select Topic', "");
                            defaultOption.innerHTML = "Select Topic";
                            selectList.append(defaultOption);
                            for (let i in noteTypesMap) {
                                if (!noteTypesMap.hasOwnProperty(i)) {
                                    continue;
                                }
                                const o = new Option(noteTypesMap[i], i);
                                o.innerHTML = noteTypesMap[i];
                                selectList.append(o);
                            }

                            selectList.prop("disabled", false);
                            $('.call-status-radios').hide();
                            $('#tasks-container').hide();
                        }
                    }

                    $('.tasks-radio').change(onTaskSelected);

                    if (patientNurseTasks.length || isEditingCompleteTask) {
                        $('#task-label').show();
                        $('#task').change(associateWithTaskChange);
                        if (isEditingCompleteTask) {
                            $('#task').trigger('change');
                        }
                    } else {
                        $('#task-label').hide();
                    }

                    function onTaskSelected(e) {
                        //get id of task
                        const task = editingTaskType ? {sub_type: editingTaskType} : patientNurseTasks.find(x => x.id === +e.currentTarget.value);
                        if (!task) {
                            return;
                        }

                        if (!editingTaskType && task.sub_type === 'Call Back') {
                            $('.call-status-radios').show();
                        } else {
                            $('.call-status-radios').hide();
                        }

                        const selectList = $('#activityKey');
                        selectList.val(taskTypeToTopicMap[task.sub_type]);
                    }

                    function tcmChange(e) {
                        let notifyCareteamEl = $('#notify-careteam');
                        let whoIsNotifiedEl = $('#who-is-notified');

                        if (e) {
                            if (e.currentTarget.checked) {
                                notifyCareteamEl.prop("checked", true);
                                notifyCareteamEl.prop("disabled", true);
                                notifyCareteamEl.trigger('change');

                                @empty($notifies_text)
                                whoIsNotifiedEl.text("{{optional($patient->billingProviderUser())->getFullName()}}");
                                @endempty
                            } else {
                                notifyCareteamEl.prop("checked", false);
                                notifyCareteamEl.prop("disabled", false);
                                notifyCareteamEl.trigger('change');

                                whoIsNotifiedEl.text("{{$notifies_text}}");
                            }
                        } else {

                        }
                    }

                    let tcmEl = $('#tcm');
                    tcmEl.change(tcmChange);
                    tcmEl.prop('checked', @json(!empty($note) && $note->isTCM));
                    tcmEl.trigger('change');

                    const validateEmailBodyUrl = '{{route('patient-email.validate', ['patient_id' => $patient->id])}}';

                    $('#newNote').submit(function (e) {
                        e.preventDefault();
                        form = this;

                        const summaryTextField = $('.text-area-summary');
                        if (summaryTextField.length > 0 && summaryTextField.is(":required") && summaryTextField.val().trim().length === 0) {
                            alert('Please enter a summary for this note.');
                            return;
                        }


                        //prevent sent if send patient email is check and email body is empty
                        if ($("[id='email-patient']").prop("checked") == true && shouldValidateEmailBody) {


                            if ($("[id='patient-email-body-input']").val() == 0) {
                                alert("Please fill out the patient email!");
                                return;
                            } else {
                                return validateEmailBody()
                            }
                        }
                        //append patient email attachments on form if the exist
                        if (formAttachments) {
                            let i = 0;
                            formAttachments.map(function (attachment) {
                                $("<input>")
                                    .attr("type", "hidden")
                                    .attr("name", "attachments[" + i + "][media_id]").val(attachment.media_id).appendTo(form);
                                $("<input>")
                                    .attr("type", "hidden")
                                    .attr("name", "attachments[" + i + "][path]").val(attachment.path).appendTo(form);
                                i++;
                            });
                        }


                        const isAssociatedWithTask = $('#task').is(':checked');
                        const callHasTask = $('.tasks-radio').is(':checked');

                        const isPhoneSession = $('#phone').is(':checked');

                        let callHasStatus = false;
                        if (userIsCCMCountable) {
                            //radio buttons
                            callHasStatus = typeof form['call_status'] !== "undefined" && typeof form['call_status'].value !== "undefined" && form['call_status'].value.length > 0;
                            callIsSuccess = typeof form['call_status'] !== "undefined" && typeof form['call_status'].value !== "undefined" && form['call_status'].value === "reached";
                        } else {
                            //checkbox
                            callIsSuccess = form['welcome_call'].checked || form['other_call'].checked;
                        }

                        if (!callHasStatus) {
                            const isCallBackTask = $('#activityKey').val() === "Call Back";
                            if ((userIsCCMCountable && isPhoneSession) || (isAssociatedWithTask && isCallBackTask)) {
                                alert('Please select whether patient was reached or not.');
                                return;
                            }
                        }

                        // ROAD-39 RN must approve care plan before making a successful welcome call
                        if (userIsCareCoach && callIsSuccess && !hasRnApprovedCarePlan) {
                            showSavingDraftModal();
                            saveDraft()
                                .then(() => {
                                    $('#saving-draft-loader').addClass('hidden');
                                    $('#saving-draft-visit-careplan-link').removeClass('disabled');
                                })
                            return;
                        }

                        if (isAssociatedWithTask && !callHasTask) {
                            alert('Please select a task to associate to.');
                            return;
                        }

                        if (isAssociatedWithTask && !isEditingCompleteTask) {
                            showTaskCompletedModal();
                            return;
                        }

                        const SECONDS_THRESHOLD = 90 * 1000;
                        const CHARACTERS_THRESHOLD = 100;
                        let showModal = false;
                        const noteBody = form['body'].value;
                        const noteBodyWithoutMeds = getNoteBodyExcludingMedications(noteBody);

                        //CPM-182:
                        // if time more than 90 seconds
                        // and (is not phone session, or phone session but not success)

                        //CPM-880:
                        // show modal only for ccm countable users
                        if (userIsCCMCountable && ((Date.now() - startDate) >= SECONDS_THRESHOLD || noteBodyWithoutMeds.length > CHARACTERS_THRESHOLD)) {

                            if (!isPhoneSession || !callIsSuccess) {
                                showModal = true;
                            }

                        }

                        if (showModal) {
                            $('#confirm-note-create').modal('show');
                            return;
                        }

                        confirmSubmitForm();
                    });

                    $(document).on("click", "#confirm-note-submit", function (event) {
                        confirmSubmitForm();
                    });

                    $(document).on("click", "#confirm-task-completed-submit", function (event) {
                        $('#task_status').val("done");
                        $('#confirm-task-completed').modal('hide');
                        confirmSubmitForm();
                    });

                    $(document).on("click", "#confirm-task-not-completed-submit", function (event) {
                        $('#task_status').val("not_done");
                        $('#confirm-task-completed').modal('hide');
                        confirmSubmitForm();
                    });

                    $(document).on("click", "#saving-draft-ok-button", function (event) {
                        $('#saving-draft').modal('hide');
                    });

                    function confirmSubmitForm() {

                        if (!conditionsAttested && callIsSuccess && userIsCareCoach) {
                            app.$emit('show-attest-call-conditions-modal');
                            return;
                        }
                        if (isSavingDraft) {
                            setTimeout(() => confirmSubmitForm(), 500);
                            return;
                        }

                        //CPM-91 and CPM-437 double submitting notes
                        if (submitted) {
                            return;
                        }

                        submitted = true;

                        clearDraftFromClientSide();
                        //when we associate a note with task, we disable the note topic
                        //we have to enable it back before posting to server,
                        //otherwise its value will not reach the server
                        $('#activityKey').prop("disabled", false);

                        if (noteId) {
                            $('<input />').attr('type', 'hidden')
                                .attr('name', "note_id")
                                .attr('value', noteId)
                                .appendTo(form);
                        }

                        form.submit();
                    }

                    function showTaskCompletedModal() {
                        $('#confirm-task-completed').modal('show');
                    }

                    function showSavingDraftModal() {
                        $('#saving-draft').modal('show');
                    }

                    const validateEmailBody = async () => {
                        return await window.axios
                            .post(validateEmailBodyUrl, {
                                //validate subject as well
                                patient_email_subject: $("[id='email-subject']").val(),
                                patient_email_body: $("[id='patient-email-body-input']").val(),
                                custom_patient_email: $("[id='custom-patient-email']").val()
                            })
                            .then((response) => {
                                if (response.data.status == 400) {
                                    app.$emit('patient-email-body-errors', response.data.messages);
                                    return false;
                                }
                                shouldValidateEmailBody = false;
                                return $('#newNote').submit();
                            })
                            .catch(err => {
                                app.$emit('patient-email-body-errors', err);
                                return false
                            });
                    };


                    /*
                    //no need since we have auto save now
                    window.addEventListener('beforeunload', (event) => {

                        if (submitted) {
                            return;
                        }

                        const noteBody = $('#note').val();
                        const trimmed = getNoteBodyExcludingMedications(noteBody);

                        if (trimmed.length) {
                            if (!confirm()) {
                                // Cancel the event as stated by the standard.
                                event.preventDefault();
                                // Chrome requires returnValue to be set.
                                event.returnValue = '';
                            }
                        }

                    });
                    */

                    function getNoteBodyExcludingMedications(noteBody) {
                        const medicationsIndex = noteBody.indexOf(MEDICATIONS_SEPARATOR);

                        if (medicationsIndex > -1) {
                            return noteBody.substring(0, medicationsIndex).trim();
                        }
                        return noteBody;
                    }


                    let isSavingDraft = false;

                    /* 2 minutes */
                    const AUTO_SAVE_INTERVAL = 1000 * 60 * 2;

                    let noteId = null;

                    @if (! empty($note))
                        noteId = '{{$note->id}}';
                            @endif

                    const saveDraftUrl = '{{route('patient.note.store.draft', ['patientId' => $patient->id])}}';

                    const saveDraft = () => {

                        const fullBody = $('#note').val();
                        const body = getNoteBodyExcludingMedications(fullBody);
                        if (!body.length) {
                            setTimeout(() => saveDraft(), AUTO_SAVE_INTERVAL);
                            return;
                        }

                        isSavingDraft = true;

                        return window.axios
                            .post(saveDraftUrl, {
                                patient_id: $('#patient_id').val(),
                                note_id: noteId,
                                type: $('#activityKey').val(),
                                general_comment: $('#general_comment').val(),
                                performed_at: $('#performed_at').val(),
                                author_id: $('#author_id').val(),
                                task_id: $('.tasks-radio:checked').val(),
                                phone: $('.phone-radios:checked').val(),
                                call_status: $('.call-status-radio:checked').val(),
                                welcome_call: $('#welcome_call').is(":checked"),
                                other_call: $('#other_call').is(":checked"),
                                medication_recon: $('#medication_recon').is(":checked"),
                                tcm: $('#tcm').is(":checked"),
                                summary: $('#summary').val(),
                                body: fullBody,
                                logger_id: $('#logger_id').val(),
                                programId: $('#programId').val(),
                                task_status: $('#task_status').val(),
                                success_story: $('#success_story').is(":checked")
                            })
                            .then((response, status) => {
                                isSavingDraft = false;
                                if (response.data && response.data.note_id) {
                                    noteId = response.data.note_id;
                                }

                                clearDraftFromClientSide();

                                setTimeout(() => saveDraft(), AUTO_SAVE_INTERVAL);
                            })
                            .catch(err => {
                                isSavingDraft = false;
                                console.error(err);
                                setTimeout(() => saveDraft(), AUTO_SAVE_INTERVAL);
                            });
                    };

                    if (!disableAutoSave) {
                        setTimeout(() => saveDraft(), AUTO_SAVE_INTERVAL);
                    }

                    const deleteElem = $('#delete-note');
                    if (deleteElem && deleteElem.length) {
                        deleteElem.click(function (e) {
                            e.preventDefault();
                            if (confirm('Are you sure?')) {
                                clearDraftFromClientSide();
                                $('#delete-form').submit();
                            }
                        });
                    }

                    function clearDraftFromClientSide() {
                        if (app.$refs.bodyComponent && app.$refs.bodyComponent.clearFromStorage) {
                            app.$refs.bodyComponent.clearFromStorage();
                        }

                        if (app.$refs.summaryInput && app.$refs.summaryInput.clearFromStorage) {
                            app.$refs.summaryInput.clearFromStorage();
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
