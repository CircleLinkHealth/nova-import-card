@extends('partials.providerUI')

@section('title', 'Patient Note')
@section('activity', 'Patient Note View')

@section('content')
    <?php
    $userTime = \Carbon\Carbon::parse($note['performed_at']);
    $userTime = $userTime->format('Y-m-d\TH:i');
    ?>

    @push('styles')
        <style type="text/css">
            div.inline {
                float: left;
            }

            .clearBoth {
                clear: both;
            }

            blockquote {
                padding: 10px 20px;
                margin: 10px 0 20px;
                font-size: 17.5px;
                border-left: 5px solid #50b2e2;
                line-height: 24px;
            }
        </style>
    @endpush

    <div class="row" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    View Note
                </div>
                {!! Form::open(array('url' => route('patient.note.send', ['patientId' => $patient->id, 'noteId' => $note['id']]), 'class' => 'form-horizontal')) !!}

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
                                        <div class="col-sm-12">
                                            @foreach($meta as $tag)
                                                <h5>
                                                    <div class="label label-{{$tag->severity}}"
                                                         @isset($tag->tooltip) data-tooltip="{{$tag->tooltip}}" @endisset>
                                                        {{ucwords($tag->title)}}
                                                    </div>
                                                </h5>
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
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="hidden" name="meta[1][meta_key]" value="comment">
                                        <textarea id="note" class="form-control" rows="10"
                                                  name="meta[1][meta_value]"
                                                  readonly>{{trim($note['comment'])}}</textarea> <br/>
                                    </div>
                                </div>

                                <div class="form-block col-md-12">
                                    <div class="row">
                                        <div class="new-note-item">
                                            @include('partials.sendToCareTeam')
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input type="hidden" name="patient_id" value="{{$patient->id}}">
                                    <input type="hidden" name="logger_id" value="{{Auth::user()->id}}">
                                    <input type="hidden" name="noteId" value="{{$note['id']}}">
                                    <input type="hidden" name="patientID" id="patientID" value="{{$patient->id}}">
                                    <input type="hidden" name="programId" id="programId" value="{{$program_id}}">
                                </div>
                                <div class="form-item form-item-spacing text-center">
                                    <div>
                                        <div class="col-sm-12">
                                            <input type="hidden" value="new_activity"/>
                                            <button id="update" name="submitAction" type="submit" value="new_activity"
                                                    class="btn btn-primary btn-lg form-item--button form-item-spacing">
                                                Return / Send
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @push('scripts')
                                    <script>
                                        $(function () {
                                            $('[data-toggle="tooltip"]').tooltip()
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
                                    </script>
                                @endpush
                                {!! Form::close() !!}

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