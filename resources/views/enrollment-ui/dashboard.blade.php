@extends('enrollment-ui.layout')

@section('title', 'Enrollment Center')
@section('activity', 'Enrollment Call')

@section('content')

    <style>

        .consented_modal {
            max-height: 100% !important;
            height: 90% !important;
            width: 80% !important;
            top: 4% !important;
        }

        .sidebar-demo-list {

            height: 24px;
            font-size: 16px;
            padding-left: 15px;
            line-height: 20px !important;

        }

        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }

        .enrollment-script {
            font-size: 20px;
        }

        /**
            NOTE: these styles are for sidebar.blade
            For some reason, there were not applied if added in that file
         */

        .counter {
            font-size: larger;
        }

        .card-subtitle {
        }

        .side-nav a {
            height: 36px;
            line-height: 36px;
        }

        .side-nav .row {
            margin-bottom: 0;
        }

        .side-nav .card {
            margin: .5rem 0 0.1rem 0;
        }

        .side-nav .card-content {
            padding: 10px;
        }

        .call-button {
            max-width: 100%;
            background: #4caf50;
        }

    </style>

    <div id="enrollment_calls">

        @include('enrollment-ui.sidebar')

        <div style="margin-left: 21%;">

            <div style="padding: 0px 10px; font-size: 16px;">

                @if($enrollee->last_call_outcome != '')
                    <blockquote>Last Call Outcome: {{$enrollee->last_call_outcome}}
                        @if($enrollee->last_call_outcome_reason != '')
                            <br/> Last Call Comment: {{$enrollee->last_call_outcome_reason}}
                        @endif
                    </blockquote>
                @endif

                <div class="enrollment-script">
                    @if($enrollee->has_copay)
                        @if($enrollee->lang == 'ES')
                            @include('enrollment-ui.script.es-has-co-pay')
                        @else
                            @include('enrollment-ui.script.en-has-co-pay')

                        @endif
                    @else
                        @if($enrollee->lang == 'ES')
                            @include('enrollment-ui.script.es-no-co-pay')
                        @else
                            @include('enrollment-ui.script.en-no-co-pay')
                        @endif
                    @endif
                </div>
            </div>

            <div style="padding: 10px; margin-bottom: 15px"></div>
            <div style="text-align: center">

            </div>
        </div>

        <!-- MODALS -->

        <!-- Success / Patient Consented -->
        @include('enrollment-ui.modals.consented')

    <!-- Unable To Contact -->
        @include('enrollment-ui.modals.utc')

    <!-- Rejected -->
        @include('enrollment-ui.modals.rejected')

    <!-- Enrollment tips -->
        @if(count($enrollee->practice->enrollmentTips))
            @include('enrollment-ui.modals.tips')
        @endif

    </div>

    <script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>
    <script src="//static.twilio.com/libs/twiliojs/1.3/twilio.min.js"></script>

    <script>

        const hasTips = @json(count($enrollee->practice->enrollmentTips) > 0);

        Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        let app = new Vue({

            el: '#enrollment_calls',

            data: {

                name: '{{ $enrollee->first_name .' '. $enrollee->last_name }}',
                lang: '{{ $enrollee->lang}}',
                provider_name: '{{ $enrollee->providerFullName }}',
                practice_name: '{{ $enrollee->practiceName }}',
                practice_phone: '{{ $enrollee->practice->outgoing_phone_number}}',
                home_phone: '{{ $enrollee->home_phone_e164 }}',
                cell_phone: '{{ $enrollee->cell_phone_e164 }}',
                other_phone: '{{ $enrollee->other_phone_e164 }}',
                address: '{{ $enrollee->address ?? 'N/A' }}',
                address_2: '{{ $enrollee->address_2 ?? 'N/A' }}',
                state: '{{ $enrollee->state ?? 'N/A' }}',
                city: '{{ $enrollee->city ?? 'N/A' }}',
                zip: '{{ $enrollee->zip ?? 'N/A' }}',
                email: '{{ $enrollee->email ?? 'N/A' }}',
                dob: '{{ $enrollee->dob ?? 'N/A' }}',
                disableHome: false,
                disableCell: false,
                disableOther: false,

                time_elapsed: 0,
                onCall: false,
                callStatus: 'Summoning Calling Gods...',
                toCall: '',

                total_time_in_system: '{!!$report->total_time_in_system !!}',

                practice_id: '{{ $enrollee->practice->id }}',
                hasTips: hasTips,
                isSoftDecline: false,
                callError: null
            },

            computed: {

                formatted_total_time_in_system: function () {

                    return new Date(1000 * this.total_time_in_system).toISOString().substr(11, 8)

                },

                //other phone computer vars
                other_phone_label: function () {

                    if (this.other_phone == '') {

                        return 'Other Phone Unknown...';

                    }

                    if (this.validatePhone(this.other_phone)) {

                        return 'Other Phone Valid!';

                    }

                    return 'Other Phone Invalid..'

                },
                other_is_valid: function () {
                    return this.validatePhone(this.other_phone)
                },
                other_is_invalid: function () {
                    return !this.validatePhone(this.other_phone)
                },

                //other phone computer vars
                home_phone_label: function () {

                    if (this.home_phone == '') {

                        return 'Home Phone Unknown...';

                    }

                    if (this.validatePhone(this.home_phone)) {

                        return 'Home Phone Valid!';

                    }

                    return 'Home Phone Invalid..'

                },
                home_is_valid: function () {
                    return this.validatePhone(this.home_phone)
                },
                home_is_invalid: function () {
                    return !this.validatePhone(this.home_phone)
                },

                //other phone computer vars
                cell_phone_label: function () {

                    if (this.cell_phone == '') {

                        return 'Cell Phone Unknown...';

                    }

                    if (this.validatePhone(this.cell_phone)) {

                        return 'Cell Phone Valid!';

                    }

                    return 'Cell Phone Invalid..'

                },
                cell_is_valid: function () {
                    return this.validatePhone(this.cell_phone)
                },
                cell_is_invalid: function () {
                    return !this.validatePhone(this.cell_phone)
                },
            },

            mounted: function () {

                this.$http.post("/twilio/token",
                    {
                        forPage: window.location.pathname,
                        practice: '{{ $enrollee->practice_id }}'
                    }
                    , function (data) {

                        console.log(data.body)

                    }).then(response => {

                        console.log(response.body)

                        this.callStatus = 'Caller Ready';
                        Materialize.toast(this.callStatus, 5000);
                        Twilio.Device.setup(response.body.token);
                        Twilio.Device.error((err) => {
                            this.callError = err.message;
                        });
                        Twilio.Device.disconnect(() => {
                            this.onCall = false;
                        });
                    }
                );

                let self = this;

                //timer
                setInterval(function () {
                    self.$data.total_time_in_system++;
                    self.$data.time_elapsed++;
                }, 1000);

                $('#consented').modal();
                $('#utc').modal();
                $('#rejected').modal({
                    complete: function () {
                        //always reset when modal is closed
                        self.isSoftDecline = false;
                    }
                });
                $('#tips').modal();
                $('select').material_select();

                if (this.hasTips) {
                    let showTips = true;
                    const tipsSettings = this.getTipsSettings();
                    if (tipsSettings) {
                        if (tipsSettings[this.practice_id] && !tipsSettings[this.practice_id].show) {
                            showTips = false;
                        }
                    }

                    $('#do-not-show-tips-again').prop('checked', !showTips);
                    if (showTips) {
                        //show the modal here
                        $('#tips-link').click();
                    }
                }
            },

            methods: {

                //triggered when cilck on Soft Decline
                //gets reset when modal closes
                softReject() {
                    this.isSoftDecline = true;
                },

                getTipsSettings() {
                    const tipsSettingsStr = localStorage.getItem('enrollment-tips-per-practice');
                    if (tipsSettingsStr) {
                        return JSON.parse(tipsSettingsStr);
                    }
                    return null;
                },

                setTipsSettings(settings) {
                    localStorage.setItem('enrollment-tips-per-practice', JSON.stringify(settings));
                },

                /**
                 * used by the tips modal
                 * @param e
                 */
                doNotShowTipsAgain(e) {
                    let settings = this.getTipsSettings();
                    if (!settings) {
                        settings = {};
                    }
                    settings[this.practice_id] = {show: !e.currentTarget.checked};
                    this.setTipsSettings(settings);
                },

                validatePhone(value) {
                    let isValid = this.isValidPhoneNumber(value)

                    if (isValid) {
                        this.isValid = true;
                        this.disableHome = true;
                        return true;
                    }
                    else {
                        this.isValid = false;
                        this.disableHome = true;
                        return false;
                    }
                },

                isValidPhoneNumber(string) {
                    //return true if string is empty
                    if (string.length === 0) {
                        return true
                    }

                    let matchNumbers = string.match(/\d+-?/g)

                    if (matchNumbers === null) {
                        return false
                    }

                    matchNumbers = matchNumbers.join('')

                    return !(matchNumbers === null || matchNumbers.length < 10 || string.match(/[a-z]/i));
                },

                call(phone, type) {
                    this.callError = null;
                    this.onCall = true;
                    this.callStatus = "Calling " + type + "..." + phone;
                    Materialize.toast(this.callStatus, 3000);
                    Twilio.Device.connect({"phoneNumber": phone});
                },

                hangUp() {
                    this.onCall = false;
                    this.callStatus = "Ended Call";
                    Materialize.toast(this.callStatus, 3000);
                    Twilio.Device.disconnectAll();
                }
            },
        });


    </script>

@stop