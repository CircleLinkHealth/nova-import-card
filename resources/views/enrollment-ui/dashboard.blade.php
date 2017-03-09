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

    </style>

    <div id="enrollment_calls">

        <ul style="width:25%; margin-top:65px;" class="side-nav fixed">
            <div class="col s12" style="width: 100%; padding: 0px 10px">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text">
                        <p>You’ve done [x] calls today and
                            Enrolled [x] patients. Nice work!
                            Elapsed time: xx:xx minutes</p>
                    </div>
                </div>
            </div>

            <span>
                    <li class="sidebar-demo-list"><span id="name">Name: @{{name}}</span></li>
                    <li class="sidebar-demo-list"><span id="cell_phone">Primary Phone: @{{primary_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Home Phone: @{{home_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Cell Phone: @{{cell_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Other Phone: @{{other_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Address: @{{address}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Email: @{{email}}</span></li>
                    <li class="sidebar-demo-list"><span id="dob">DOB: @{{dob}}</span></li>
                 </span>

            <hr>

            <li class="sidebar-demo-list"><span id="billing_provider">Dr. John Doe</span></li>

        </ul>

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

            },

            mounted: function () {

                $('#consented').modal();
                $('#utc').modal();
                $('#rejected').modal();
                $('select').material_select();

            },

            methods: {



            }


        });

    </script>

    <script src="{{ asset('/js/idle-timer.min.js') }}"></script>
    @include('partials.providerUItimer')

@stop