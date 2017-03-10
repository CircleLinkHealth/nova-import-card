@extends('enrollment-ui.layout')

@section('title', 'Enrollment Calls')
@section('activity', 'Enrollment Call')

@section('content')

    <style>

        .sidebar-demo-list {

            height: 27px;
            font-size: 17px;
            padding-left: 15px;

        }

        .valid {
            color: green;
        }

        .invalid {
            color: red;
        }

    </style>

    <div id="enrollment_calls">

        @include('enrollment-ui.sidebar')

        <div style="margin-left: 375px; margin-top: 10px;">
            <a class="waves-effect waves-light btn" href="#consented">Patient Consented</a>
            <a class="waves-effect waves-light btn" href="#utc" style="background: #ecb70e">No Answer /
                Requested Call Back</a>
            <a class="waves-effect waves-light btn" href="#rejected" style="background: red;">Patient
                Declined</a>
        </div>

        <!-- MODALS -->

        <!-- Success / Patient Consented -->
        @include('enrollment-ui.modals.consented')

    <!-- Unable To Contact -->
        @include('enrollment-ui.modals.utc')

    <!-- Rejected -->
        @include('enrollment-ui.modals.rejected')

    </div>

    <script src="https://unpkg.com/vue@2.1.3/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/vue.resource/1.2.0/vue-resource.min.js"></script>

    <script>

        Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        let app = new Vue({

            el: '#enrollment_calls',

            data: {

                name: '{{ $enrollee->first_name ?? ''. $enrollee->last_name }}',
                provider_name: '{{ $enrollee->providerFullName }}',
                primary_phone: '{{ $enrollee->primary_phone ?? 'N/A' }}',
                home_phone: '{{ $enrollee->home_phone ?? 'N/A' }}',
                cell_phone: '{{ $enrollee->cell_phone ?? 'N/A' }}',
                other_phone: '{{ $enrollee->other_phone ?? 'N/A' }}',
                address: '{{ $enrollee->address ?? 'N/A' }}',
                address_2: '{{ $enrollee->address_2 ?? 'N/A' }}',
                state: '{{ $enrollee->state ?? 'N/A' }}',
                city: '{{ $enrollee->city ?? 'N/A' }}',
                zip: '{{ $enrollee->zip ?? 'N/A' }}',
                email: '{{ $enrollee->email ?? 'N/A' }}',
                dob: '{{ $enrollee->dob ?? 'N/A' }}',
                phone_regex: /^\d{3}-\d{3}-\d{4}$/,

                total_time_in_system: '{!!$report->total_time_in_system !!}'

            },

            computed: {

                formatted_total_time_in_system: function () {

                    return new Date(1000 * this.total_time_in_system).toISOString().substr(11, 8)

                },

                //primary phone computer vars
                primary_phone_label: function () {

                    if (this.primary_phone.match(this.phone_regex)) {

                        return 'Primary Phone Valid!';

                    }

                    return 'Primary Phone Invalid..'

                },

                primary_is_valid: function () {
                    return this.primary_phone.match(this.phone_regex);
                },
                primary_is_invalid: function () {
                    return !this.primary_phone.match(this.phone_regex);
                },

                //other phone computer vars
                other_phone_label: function () {

                    if (this.other_phone.match(this.phone_regex)) {

                        return 'Other Phone Valid!';

                    }

                    return 'Other Phone Invalid..'

                },

                other_is_valid: function () {
                    return this.other_phone.match(this.phone_regex);
                },
                other_is_invalid: function () {
                    return !this.other_phone.match(this.phone_regex);
                },

                //other phone computer vars
                home_phone_label: function () {

                    if (this.home_phone.match(this.phone_regex)) {

                        return 'Home Phone Valid!';

                    }

                    return 'Home Phone Invalid..'

                },

                home_is_valid: function () {
                    return this.home_phone.match(this.phone_regex);
                },
                home_is_invalid: function () {
                    return !this.home_phone.match(this.phone_regex);
                },

                //other phone computer vars
                cell_phone_label: function () {

                    if (this.cell_phone.match(this.phone_regex)) {

                        return 'Cell Phone Valid!';

                    }

                    return 'Cell Phone Invalid..'

                },

                cell_is_valid: function () {
                    return this.cell_phone.match(this.phone_regex);
                },
                cell_is_invalid: function () {
                    return !this.cell_phone.match(this.phone_regex);
                },

            },

            mounted: function () {

                let self = this;

                setInterval(function () {
                    self.$data.total_time_in_system++;
                }, 1000);

                $('#consented').modal();
                $('#utc').modal();
                $('#rejected').modal();
                $('select').material_select();

            },

            methods: {

                //implement!
                validatePhone(VAL, name){

                    //matches format +11111111111

                    if (VAL.match(this.phone_regex)) {
                        this.isValid = true;
                        this.isInValid = false;
                        this.primary_phone_label = name + ' Valid!';
                        return true;
                    }
                    else {
                        this.isValid = false;
                        this.isInValid = true;
                        this.primary_phone_label = name + ' Invalid..';
                        return false;
                    }

                }
            },
        });


    </script>

    {{--<script src="{{ asset('/js/idle-timer.min.js') }}"></script>--}}
    {{--@include('partials.providerUItimer')--}}

@stop