@extends('provider.layouts.dashboard')

@section('title', 'Notifications and Settings')

@section('module')


    @include('errors.materialize-errors')


    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.notifications', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id'=>'practice-settings-form']) !!}

            <button type="submit"
                    form="practice-settings-form"
                    class="btn blue waves-effect waves-light col s4"
                    onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
                Update Preferences
            </button>

            <div class="row">
                <div class="input-field col s12"><h6>Settings</h6></div>

                <div class="input-field col s6">
                    <input name="settings[auto_approve_careplans]" type="checkbox" id="auto_approve_careplans"
                           value="1" @if($practiceSettings->auto_approve_careplans){{'checked'}}@endif>
                    <label for="auto_approve_careplans">Auto Approve Care Plans</label>
                </div>

                <div class="input-field col s6">
                    <input name="settings[rn_can_approve_careplans]" type="checkbox" id="rn_can_approve_careplans"
                           value="1" @if($practiceSettings->rn_can_approve_careplans){{'checked'}}@endif>
                    <label for="rn_can_approve_careplans">RNs Can Approve Care Plans</label>
                </div>

                <div class="input-field col s4" style="margin-top: 3rem;">
                    {{ Form::select('settings[careplan_mode]', ['web'=>'Web','pdf'=>'PDF'], $practiceSettings->careplan_mode) }}
                    <label>Starting CarePlan Mode</label>
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
                    <label for="wekly-summary-recipients">Weekly Organization Summary Recipients (comma separated, w/ spaces after comma)</label>
                    <small>The emails above will receive weekly summary reports.</small>
                </div>
            </div>
        </div>


        <button type="submit"
                form="practice-settings-form"
                class="btn blue waves-effect waves-light col s4"
                onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
            Update Preferences
        </button>

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