<div class="form-group">
    <div class="col-sm-12">
        <label>Send Note To:</label>
    </div>
    <div class="col-sm-12 no-padding-left" style="padding-top: 10px">
        <div class="col-sm-4">
            <input type="checkbox" id="notify-circlelink-support" name="notify_circlelink_support" value="1">
            <label for="notify-circlelink-support"><span> </span>{{$patient->primaryPractice->saasAccountName()}}
                Support</label>
        </div>
        <div class="col-sm-4">
            @empty($note_channels_text)
                <b>This Practice has <em>Forwarded Note Notifications</em> turned off. Please notify CirleLink support.</b>
            @else
                <div class="col-sm-12" style="position: absolute">
                    @empty($notifies_text)
                        <p style="color: red;">
                            No provider selected to receive email alerts. Use the add ("+" sign) or edit (pencil) icons in
                            the
                            <strong>{{link_to_route('patient.careplan.print', '"Care Team"', ['patientId' => $patient->id])}}</strong>
                            section of
                            the {{link_to_route('patient.careplan.print', 'View CarePlan', ['patientId' => $patient->id])}}
                            page to
                            add or edit providers to receive email alerts.
                        </p>
                    @else

                        <input type="checkbox" id="notify-careteam" name="notify_careteam"
                               @empty($note_channels_text) disabled="disabled"
                               @endempty value="1">
                        <label for="notify-careteam" style="display: inline-block;"><span></span>Provider/CareTeam

                        </label>
                        <div class="label" data-tooltip="Notifies: {{ $notifies_text }} via {{ $note_channels_text }}">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:#50b2e2"></i>
                        </div>

                </div>


            @endempty
            @endempty

        </div>
        <div class="col-sm-4" style="text-align: right">
            @if(Route::is('patient.note.view'))
                <input type="hidden" value="new_activity"/>
                <button id="update" name="submitAction" type="submit" value="new_activity"
                        class="btn btn-primary btn-sm">
                    Send / Return
                </button>
            @endif
        </div>
        <div class="col-sm-12">
            <hr>
        </div>

    </div>

    <div class="col-sm-12">
        <div class="form-group load-hidden">
            <div class="col-sm-12 no-padding-left">
                <i class="fa fa-star" style="font-size:12px; margin-right: 8px"></i>
                <label for="summary">
                    Communication to Practice @if(isset($note['summary_type']) && !empty($note['summary_type']))<span style="color: #50b2e2"><b>({{$note['summary_type']}})</b></span>@endif
                </label>
            </div>
            <div class="col-sm-12 no-padding-left no-padding-right">
                @if(Route::is('patient.note.create'))
                    <div class="col-sm-1 no-padding-left custom-radio">
                        <input id='fyi' type="radio" name="summary_type" value="{{\App\Note::SUMMARY_FYI}}">
                        <label for="fyi"><span> </span>FYI</label>
                    </div>
                    <div class="col-sm-11 no-padding-left custom-radio">
                        <input id="to-do" type="radio" name="summary_type"  value="{{\App\Note::SUMMARY_TODO}}">
                        <label for="to-do"><span> </span>To-do</label>
                        <br>
                    </div>
                    <div class="col-sm-12 no-padding-left no-padding-right">
                        <persistent-textarea ref="summaryInput" storage-key="notes-summaries:{{$patient->id}}:add"
                                             id="summary"
                                             class-name="form-control text-area-summary" :rows="2" :cols="100"
                                             :max-chars="280"
                                             placeholder="Write a summary here to describe what happened in the call. This is generally 1-2 sentences to highlight the important tasks for the doctor."
                                             value="{{ optional($note)->summary ?? '' }}"
                                             name="summary"></persistent-textarea>
                    </div>
                @elseif(isset($note['summary']) && !empty($note['summary']))
                    <div class="col-sm-12" style="padding-top: 10px">
                        <span><strong>{{trim($note['summary'])}}</strong></span>
                    </div>
                @endif
                <br>
            </div>
        </div>
    </div>

</div>

@push('styles')
    <style>
        .load-hidden {
            display: none;
        }

        .no-padding-left {
            padding-left: 0px;
        }

        .no-padding-right {
            padding-right: 0px;
        }

        .custom-radio {
            padding-top: 10px;
        }

    </style>
@endpush

@push('scripts')
    <script>
        const isCreateNotePage = @json(Route::is('patient.note.create'));
        const hasSummary = @json(isset($note['summary']) && !empty($note['summary']));
        if (isCreateNotePage) {
            (function ($) {
                //hacky way to display summary input required when notify-careteam is checked, and also make summary required
                $('#notify-careteam').change(function (e) {

                    if (typeof App === 'undefined' || typeof Vue === 'undefined') {
                        return;
                    }

                    //might be called before summaryInput component is created
                    if (!App.$refs.summaryInput) {
                        return;
                    }

                    Vue.config.silent = true;

                    if (e.currentTarget.checked) {
                        $('.load-hidden').show();
                    }
                    else {
                        $('.load-hidden').hide();
                    }

                    App.$refs.summaryInput.$props.required = e.currentTarget.checked;
                    App.$refs.summaryInput.$forceUpdate();

                    Vue.config.silent = false;
                });
            })(jQuery);
        }
        else if (hasSummary) {
            $('.load-hidden').show();
        }

        $('#notify-careteam').change(function (e) {
            setSubmitText();
        });

        $('#notify-circlelink-support').change(function (e) {
            setSubmitText();
        });

        function setSubmitText() {
            const text = ($('#notify-circlelink-support').is(':checked') || $('#notify-careteam').is(':checked')) ? 'Save / Send Note' : 'Save Note';
            $('#Submit').text(text);
        }


        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                animation: true
            })
        });


    </script>
@endpush