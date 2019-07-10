<div class="form-group">
    <div class="col-sm-12">
        <label>Send To:</label>
    </div>
    <div class="col-sm-12">
        <input type="checkbox" id="notify-circlelink-support" name="notify_circlelink_support" value="1">
        <label for="notify-circlelink-support"><span> </span>{{$patient->primaryPractice->saasAccountName()}}
            Support</label>
    </div>
    <div class="col-sm-12">
        @empty($note_channels_text)
            <b>This Practice has <em>Forwarded Note Notifications</em> turned off. Please notify CirleLink support.</b>
        @else
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
                <label for="notify-careteam" style="display: inline-block;"><span> </span>Provider/CareTeam
                    (
                    <b>Notifies:</b>
                    <div id="who-is-notified" style="display: inline-block; text-indent: 0;">{{ $notifies_text }}</div>
                    <b><u>via</u></b>
                    <div style="display: inline-block; text-indent: 3px;">{{ $note_channels_text }}</div>
                    )
                </label>

                <div class="form-group load-hidden">
                    <label for="summary">
                        Communication to Practice
                    </label>
                    <div class="col-sm-12">
                        @if(Route::is('patient.note.create'))
                            <persistent-textarea ref="summaryInput" storage-key="notes-summaries:{{$patient->id}}:add"
                                                 id="summary"
                                                 class-name="form-control text-area-summary" :rows="3" :cols="100"
                                                 :max-chars="280"
                                                 placeholder="Enter Note Summary..."
                                                 value="{{ optional($note)->summary ?? '' }}"
                                                 name="summary"></persistent-textarea>
                        @elseif(isset($note['summary']) && !empty($note['summary']))
                            <textarea id="note" class="form-control" rows="3"
                                      name="summary"
                                      readonly>{{trim($note['summary'])}}
                            </textarea>
                        @endif
                        <br>
                    </div>
                </div>
            @endempty
        @endempty

    </div>
</div>

@push('styles')
    <style>
        .load-hidden {
            display: none;
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

    </script>
@endpush