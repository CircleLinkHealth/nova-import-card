@extends('enrollment-ui.layout')

@section('title', 'Enrollment Calls')

<style>

    .sidebar-demo-list {

        height: 27px;
        font-size: 17px;
        padding-left: 15px;

    }

</style>

@section('content')
    <div id="enrollment_calls">
        <ul style="width:25%; margin-top:65px;" class="side-nav fixed">
            <div class="col s12" style="width: 100%; padding: 0px 10px">
                <div class="card blue-grey darken-1">
                    <div class="card-content white-text">
                        <p>You’ve done [5] calls today and
                            Enrolled [2] patients. Nice work!
                            Elapsed time: 10:35 minutes</p>
                    </div>
                </div>
            </div>

            <span>
            <li class="sidebar-demo-list"><span id="name" class="editable">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="cell_phone" class="editable">(972) 762 2642</span></li>
            <li class="sidebar-demo-list"><span id="home_phone" class="editable">(201) 201 2011</span></li>
            <li class="sidebar-demo-list"><span id="address" class="editable">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="email" class="editable">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="family" class="editable">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="ethnicity" class="editable">Rohan Maheshwari</span></li>
            <li class="sidebar-demo-list"><span id="dob" class="editable">Rohan Maheshwari</span></li>
         </span>

        </ul>
    </div>

@stop


@section('scripts')
    <script>

        Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        let app = new Vue({


            el: '#enrollment_calls',


            data: {},

            methods: {}


        });

    </script>
@stop