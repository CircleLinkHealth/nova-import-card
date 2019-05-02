@extends('provider.layouts.dashboard')

@section('title', 'Notifications and Settings')

@section('module')


    @include('errors.materialize-errors')

    @push('styles')
        <style>
            .mg-bottom-minus-30 {
                margin-bottom: -30px;
            }
        </style>
    @endpush


    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.notifications', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id'=>'practice-settings-form']) !!}

            <div class="row">
                <button type="submit"
                        form="practice-settings-form"
                        class="btn blue waves-effect waves-light col s4"
                        onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
                    Update Preferences
                </button>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <input name="settings[auto_approve_careplans]" type="checkbox" id="auto_approve_careplans"
                           value="1" @if($practiceSettings->auto_approve_careplans){{'checked'}}@endif>
                    <label for="auto_approve_careplans">Auto Approve Care Plans</label>
                </div>

                @if($practice->external_id !== null)
                    <div class="input-field col s12">
                        <input name="settings[api_auto_pull]" type="checkbox" id="api_auto_pull"
                               value="1" @if($practiceSettings->api_auto_pull == 1){{'checked'}}@endif>
                        <label for="api_auto_pull">Auto Pull Eligible Patients from Athena</label>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="input-field col s12"><h6>Twilio</h6></div>

                <div class="input-field col s6">

                    @if (config('services.twilio.enabled'))
                        <input name="settings[twilio_enabled]" type="checkbox"
                               id="twilio_enabled"
                               value="1" @if($practiceSettings->twilio_enabled){{'checked'}}@endif />
                    @else
                        <input name="settings[twilio_enabled]" type="checkbox"
                               disabled="disabled"
                               id="twilio_enabled"
                               value="1"/>
                    @endif

                    <label for="twilio_enabled">Use Twilio for Calls</label>

                </div>

                <div class="input-field col s6">

                    @if(config('services.twilio.enabled') && config('services.twilio.allow-recording'))
                        <input name="settings[twilio_recordings_enabled]" type="checkbox"
                               id="twilio_recordings_enabled"
                               value="1" @if($practiceSettings->twilio_recordings_enabled){{'checked'}}@endif />
                    @else
                        <input name="settings[twilio_recordings_enabled]" type="checkbox"
                               disabled="disabled"
                               id="twilio_recordings_enabled"
                               value="1"/>
                    @endif

                    <label for="twilio_recordings_enabled">Record calls</label>

                </div>
            </div>

            <div class="row">
                <div class="input-field col s12"><h6>Direct Mail Notifications</h6></div>

                <div class="input-field col s6">
                    <input name="settings[dm_pdf_careplan]" type="checkbox" id="dm_pdf_careplan"
                           value="1" @if($practiceSettings->dm_pdf_careplan){{'checked'}}@endif>
                    <label for="dm_pdf_careplan">Send PDF Care Plans</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[dm_pdf_notes]" type="checkbox"
                           id="dm_pdf_notes"
                           value="1" @if($practiceSettings->dm_pdf_notes){{'checked'}}@endif>
                    <label for="dm_pdf_notes">Send PDF Notes</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[dm_audit_reports]" type="checkbox"
                           id="dm_audit_reports"
                           value="1" @if($practiceSettings->dm_audit_reports){{'checked'}}@endif>
                    <label for="dm_audit_reports">Send Audit Reports</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[dm_careplan_approval_reminders]" type="checkbox"
                           id="dm_careplan_approval_reminders"
                           value="1" @if($practiceSettings->dm_careplan_approval_reminders){{'checked'}}@endif>
                    <label for="dm_careplan_approval_reminders">Send Care Plan Approval Reminders</label>
                </div>

                @if($practice->hasAWVServiceCode())
                <div class="input-field col s6">
                    <input name="settings[dm_awv_reports]" type="checkbox"
                           id="dm_awv_reports"
                           value="1" @if($practiceSettings->dm_awv_reports){{'checked'}}@endif>
                    <label for="dm_awv_reports">Send AWV Reports</label>
                </div>
                @endif

            </div>

            <div class="row">
                <div class="input-field col s12"><h6>Efax Notifications</h6></div>

                <div class="input-field col s6">
                    <input name="settings[efax_pdf_careplan]" type="checkbox" id="efax_pdf_careplan"
                           value="1" @if($practiceSettings->efax_pdf_careplan){{'checked'}}@endif>
                    <label for="efax_pdf_careplan">Send PDF Care Plans</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[efax_pdf_notes]" type="checkbox"
                           id="efax_pdf_notes"
                           value="1" @if($practiceSettings->efax_pdf_notes){{'checked'}}@endif>
                    <label for="efax_pdf_notes">Send PDF Notes</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[efax_audit_reports]" type="checkbox"
                           id="efax_audit_reports"
                           value="1" @if($practiceSettings->efax_audit_reports){{'checked'}}@endif>
                    <label for="efax_audit_reports">Send Audit Reports</label>
                </div>

                @if($practice->hasAWVServiceCode())
                    <div class="input-field col s6">
                        <input name="settings[efax_awv_reports]" type="checkbox"
                               id="efax_awv_reports"
                               value="1" @if($practiceSettings->efax_awv_reports){{'checked'}}@endif>
                        <label for="efax_awv_reports">Send AWV Reports</label>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="input-field col s12"><h6>Email Notifications</h6></div>

                <div class="input-field col s6">
                    <input name="settings[email_careplan_approval_reminders]" type="checkbox"
                           id="email_careplan_approval_reminders"
                           value="1" @if($practiceSettings->email_careplan_approval_reminders){{'checked'}}@endif>
                    <label for="email_careplan_approval_reminders">CarePlan Approvals Reminders</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[email_note_was_forwarded]" type="checkbox" id="email_note_was_forwarded"
                           value="1" @if($practiceSettings->email_note_was_forwarded){{'checked'}}@endif>
                    <label for="email_note_was_forwarded">Email Note Was Forwarded</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[email_weekly_report]" type="checkbox" id="email_weekly_report"
                           value="1" @if($practiceSettings->email_weekly_report){{'checked'}}@endif>
                    <label for="email_weekly_report">MDs Receive Weekly Reports</label>
                </div>

                @if($practice->hasAWVServiceCode())
                    <div class="input-field col s6">
                        <input name="settings[email_awv_reports]" type="checkbox"
                               id="email_awv_reports"
                               value="1" @if($practiceSettings->email_awv_reports){{'checked'}}@endif>
                        <label for="email_awv_reports">Send AWV Reports</label>
                    </div>
                @endif

                <div class="input-field col s12" style="margin-top: 3rem;">
                    <textarea id="invoice-recipients" name="invoice_recipients"
                              class="materialize-textarea">{{$practice->invoice_recipients}}</textarea>
                    <label for="invoice-recipients">Invoice Recipients (comma separated, w/ spaces after comma)</label>
                    @if($invoiceRecipients)
                        <small>The emails above will receive invoices, in addition to {{$invoiceRecipients}}.</small>
                    @else
                        <small>The emails above will receive invoices.</small>
                    @endif
                </div>

                <div class="input-field col s12" style="margin-top: 3rem;">
                    <textarea id="wekly-summary-recipients" name="weekly_report_recipients"
                              class="materialize-textarea">{{$practice->weekly_report_recipients}}</textarea>
                    <label for="wekly-summary-recipients">Weekly Organization Summary Recipients (comma separated, w/
                        spaces after comma)</label>
                    <small>The emails above will receive weekly summary reports.</small>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s4" style="margin-top: 3rem;">
                    {{ Form::select('settings[careplan_mode]', ['web'=>'Web','pdf'=>'PDF'], $practiceSettings->careplan_mode) }}
                    <label>Starting CarePlan Mode</label>
                </div>

                <div class="input-field col s4" style="margin-top: 3rem;">
                    {{ Form::select('settings[bill_to]', ['practice'=>'Practice','provider'=>'Provider'], $practiceSettings->bill_to) }}
                    <label>Bill to:</label>
                </div>

                <div class="input-field col s4" style="margin-top: 3rem;">
                    <input type="number" name="settings[note_font_size]" id="note_font_size"
                           value="{{$practiceSettings->note_font_size ?? config('snappy.pdf.options.zoom')}}" step="0.1"
                           min="0.1" max="2.0">
                    <label for="note_font_size">PDF Note Font Size</label>
                </div>
            </div>
        </div>


        <div class="row">
            <button type="submit"
                    form="practice-settings-form"
                    class="btn blue waves-effect waves-light col s4"
                    onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
                Update Preferences
            </button>
        </div>

        {!! Form::close() !!}

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('select').material_select();
        });
    </script>
@endpush