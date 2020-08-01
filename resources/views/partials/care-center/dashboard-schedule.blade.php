@if($nurse)
    @push('styles')
        <style>
            /*.nurse-dashboard-schedule {*/
            /*    background-color: rgba(80, 178, 226, 0.03);*/
            /*    border: 1px solid rgba(204, 204, 204, .8);*/
            /*    border-radius: 10px;*/
            /*    box-shadow: rgba(128, 128, 128, 0.1) 0px 1px 2px;*/
            /*    padding: 0px 15px 15px;*/
            /*}*/
        </style>
    @endpush
    <notifications class="text-left"></notifications>
    <div class="container-fluid"
         style="width: 90%; margin-left: 5%; margin-bottom: 5%">
        <div class="row" style="text-align: center">
            <h3>
                Your Schedule ({{auth()->user()->timezone_abbr}})
            </h3>
        </div>
        <div class="col-lg-12">
            <nurse-schedule-calendar
                    :auth-data="{{json_encode($authData)}}"
                    :today="{{json_encode(\Carbon\Carbon::parse(today())->toDateString())}}">
            </nurse-schedule-calendar>
        </div>

    </div>
@endif