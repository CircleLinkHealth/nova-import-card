@extends('partials.providerUI')

@section('title', 'Patient Note')
@section('activity', 'Patient Note View')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
              integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
              crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Roboto:500&display=swap" rel="stylesheet">
        <style type="text/css">
            div.inline {
                float: left;
            }

            .clearBoth {
                clear: both;
            }

            .edgy-button {
                border-radius: 3px;
            }

            blockquote {
                padding: 10px 20px;
                margin: 10px 0 20px;
                font-size: 17.5px;
                border-left: 5px solid #50b2e2;
                line-height: 24px;
            }

            body {
                font-family: 'Roboto', sans-serif !important;
            }

            b {
                font-weight: bolder;
            }

            .meta-tags {
                line-height: 1.2;
                margin-top: 10px;
                margin-bottom: 10px;
                margin-right: 10px;
                display: table-cell;
            }

            .download-note-pdf-btn {
                font-size: 18px;
                color: #fff;
                background-color: #4fb2e3;
                border-color: #4fb2e3;
            }
        </style>
    @endpush

    <div class="row" style="margin-top:30px;">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
            @include('errors.errors')
        </div>
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    View Note

                    @if(isDownloadingNotesEnabledForUser(auth()->id()))
                        <div class="pull-right">
                            <a href="{{ route('patient.note.download', ['patientId' => $patient->id, 'noteId' => $note['id'], 'format' => 'pdf']) }}"
                               class="download-note-pdf-btn tooltip-top"
                               data-tooltip="Download Note as PDF"
                            >
                                <span class="glyphicon glyphicon-download" aria-hidden="true"></span>
                            </a>
                        </div>
                    @endif
                </div>

                {!! Form::open(array('url' => route('patient.note.send', ['patientId' => $patient->id, 'noteId' => $note['id']]), 'class' => 'form-horizontal', 'id' => 'viewNote')) !!}

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
                                                    class="selectpicker dropdownValid form-control"
                                                    data-size="10" disabled>
                                                <option value="{{$note['type']}}"> {{$note['type']}} </option>
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
                                                   class="selectpicker form-control"
                                                   data-width="95px" data-size="10" list max="{{$userTime}}"
                                                   value="{{$userTime}}"
                                                   disabled>
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
                                            <select id="performedBy" name="provider_id"
                                                    class="selectpicker dropdown Valid form-control" data-size="10"
                                                    disabled>
                                                <option value=""> {{$note['provider_name']}}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($meta)
                        <div class="form-block col-md-6">
                            <div class="row">
                                <div class="new-note-item">
                                    <div class="form-group">
                                        <div class="col-sm-12 form-group">

                                        </div>
                                        <div class="col-sm-12">
                                            @foreach($meta as $tag)
                                                <div style=" display: inline">
                                                    <div class="label label-{{$tag->severity}} meta-tags"
                                                         @isset($tag->tooltip) data-tooltip="{{$tag->tooltip}}" @endisset>
                                                        {{ucwords($tag->title)}}
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(is_array($hasReaders))
                                                @foreach($hasReaders as $key => $value)
                                                    <h5>
                                                        <div style="margin-right: 2px; margin-bottom: 4px;"
                                                             class="inline label label-success"
                                                             data-tooltip="{{$value}}">
                                                            <div style="padding: 1px; padding-left: 0"
                                                                 class="label label-success">
                                                                <span class="glyphicon glyphicon-eye-open"
                                                                      aria-hidden="true"></span>
                                                                @if($key == $note['provider_name'])
                                                                    (B.P.)
                                                                @endif
                                                            </div>
                                                            {{$key}}
                                                        </div>
                                                    </h5>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="form-block col-md-12">
                        <div class="row">
                            <div class="new-note-item">
                                <div class="form-block col-md-12">
                                    <div class="row">
                                        @if(! auth()->user()->isCareCoach() || (auth()->user()->isCareCoach() && app(App\Policies\CreateNoteForPatient::class)->can(auth()->id(), $patient->id)))
                                        <div class="new-note-item">
                                                @include('partials.sendToCareTeam')
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Full Note -->
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="hidden" name="meta[1][meta_key]" value="comment">
                                        <i class="fas fa-book" style="font-size:12px; margin-right: 10px"></i>
                                        <label for="meta[1][meta_value]">
                                            <span style="color: #50b2e2">{{$author->getFullName()}}</span> wrote a note
                                            on <span style="color: lightgrey">{{$note['created_at']}}</span>
                                        </label>
                                        <textarea id="note" class="form-control" rows="10"
                                                  name="meta[1][meta_value]"
                                                  readonly>{{trim($note['comment'])}}</textarea> <br/>
                                    </div>
                                </div>

                                <div class="form-group col-sm-4">
                                    <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                    <input type="hidden" name="logger_id" value="{{Auth::user()->id}}">
                                    <input type="hidden" name="noteId" value="{{$note['id']}}">
                                    <input type="hidden" name="patientID" id="patientID" value="{{$patient->id}}">
                                    <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                </div>

                                @push('scripts')
                                    <script>
                                        $(function () {
                                            $('[data-toggle="tooltip"]').tooltip()
                                        });

                                        let shouldValidateEmailBody = true;
                                        let form;
                                        let formAttachments = null;
                                        const validateEmailBodyUrl = '{{route('patient-email.validate', ['patient_id' => $patient->id])}}';

                                        function withApp(callback) {
                                            if (typeof App === 'undefined') {
                                                setTimeout(() => withApp(callback), 500);
                                                return;
                                            }
                                            callback(App);
                                        }

                                        $(document).ready(function () {
                                            withApp((app) => {
                                                app.$on('file-upload', (attachments) => {
                                                    formAttachments = attachments;
                                                });

                                                $('#viewNote').submit(function (e) {
                                                    e.preventDefault();
                                                    form = this;
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
                                                    form.submit();
                                                });

                                                const validateEmailBody = async () => {
                                                    return await window.axios
                                                        .post(validateEmailBodyUrl, {
                                                            patient_emaile_subject: $("[id='email-subject']").val(),
                                                            patient_email_body: $("[id='patient-email-body-input']").val()
                                                        })
                                                        .then((response) => {
                                                            if (response.data.status == 400) {
                                                                app.$emit('patient-email-body-errors', response.data.messages);
                                                                return false;
                                                            }
                                                            shouldValidateEmailBody = false;
                                                            return $('#viewNote').submit();
                                                        })
                                                        .catch(err => {
                                                            app.$emit('patient-email-errors', err);
                                                            return false
                                                        });
                                                };
                                            });

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
                                        });
                                    </script>
                                @endpush
                                {!! Form::close() !!}
                                @if(authUserCanSendPatientEmail())
                                    @include('wpUsers.patient.note.patient-emails')
                                @endif
                                <div class="col-sm-12">
                                    @include('wpUsers.patient.note.manage-addendums')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
