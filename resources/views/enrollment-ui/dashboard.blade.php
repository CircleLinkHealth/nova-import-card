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

    </style>

    <div id="enrollment_calls">

        @include('enrollment-ui.sidebar')

        <div style="margin-left: 26%;">

            <div style="text-align: center">
                <h5> @{{ name }} </h5>

                <div v-if="onCall === true" style="text-align: center">
                    <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                class="material-icons left">call_end</i>Hang Up</a>
                </div>
                <div v-else style="text-align: center">
                    @if($enrollee->home_phone_e164 != '')
                        <a v-on:click="call(home_phone, 'Home')" class="waves-effect waves-light btn"
                           style="background: #4caf50"><i
                                    class="material-icons left">phone</i>Home</a>
                    @endif
                    @if($enrollee->cell_phone_e164 != '')
                        <a v-on:click="call(cell_phone, 'Cell')" class="waves-effect waves-light btn"
                           style="background: #4caf50"><i
                                    class="material-icons left">phone</i>Cell</a>
                    @endif
                    @if($enrollee->other_phone_e164 != '')
                        <a v-on:click="call(other_phone, 'Other')" class="waves-effect waves-light btn"
                           style="background: #4caf50"><i
                                    class="material-icons left">phone</i>Other</a>
                    @endif
                </div>
            </div>

            <div style="margin-top: 10px; text-align: center">
                <a class="waves-effect waves-light btn" href="#consented">Patient Consented</a>
                <a class="waves-effect waves-light btn" href="#utc" style="background: #ecb70e">No Answer (Voicemail
                    Script) /
                    Requested Call Back</a>
                <a class="waves-effect waves-light btn" href="#rejected" style="background: red;">Patient
                    Declined</a>

                @if(count($enrollee->practice->enrollmentTips))
                    <a class="waves-effect waves-light btn" href="#tips" id="tips-link"
                       style="background: black;">Tips</a>
                @endif
            </div>

            <div v-if="onCall === true" style="text-align: center">
                <blockquote>Call Status: @{{ this.callStatus }}</blockquote>
            </div>

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
                hasTips: hasTips
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
                $('#rejected').modal();
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
                    this.callStatus = "Calling " + type + "..." + phone;
                    Materialize.toast(this.callStatus, 3000);
                    Twilio.Device.connect({"phoneNumber": phone});
                    this.onCall = true;
                },

                hangUp() {
                    Twilio.Device.disconnectAll();
                    this.callStatus = "Ended Call";
                    Materialize.toast(this.callStatus, 3000);
                    this.onCall = false;
                }
            },
        });


    </script>

@stop