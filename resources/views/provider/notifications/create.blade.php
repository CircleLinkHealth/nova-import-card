@extends('provider.layouts.dashboard')

@section('title', 'Notification Settings')

@section('module')

    @include('errors.errors')

    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.notifications', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12']) !!}

            <div class="row">
                <div class="input-field col s12">Settings</div>

                <div class="input-field col s6">
                    <input name="settings[auto_approve_careplans]" type="checkbox" id="auto_approve_careplans"
                           value="1" @if($practiceSettings->auto_approve_careplans){{'checked'}}@endif>
                    <label for="auto_approve_careplans">Auto Approve Care Plans</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">Direct Mail Notifications</div>

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
                <div class="input-field col s12">Efax Notifications</div>

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
                <div class="input-field col s12">Email Notifications</div>

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
            </div>
        </div>


        <button class="btn blue waves-effect waves-light col s12"
                id="update-practice"
                onclick="Materialize.toast('{{$practice->display_name}} was successfully updated.', 4000)">
            Update Practice
        </button>

        {!! Form::close() !!}

    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('select').select2();
    </script>
@endsection