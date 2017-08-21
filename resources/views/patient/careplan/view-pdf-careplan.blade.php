@extends('partials.providerUI')

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')

<style>
    .revert-btn {
        background-color: #c72e29;
        border-radius: 0 !important;
        color: white;
    }
</style>

@section('content')
    <?php
    //to force recompile view
    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    ?>

    <div id="v-pdf-careplans" class="container">
        <pdf-careplans></pdf-careplans>

        <div class="row" style="padding-top: 20%;">
            <div class="col-md-12 text-center">
                <a href="{{route('switch.to.web.careplan', ['carePlanId' => $patient->carePlan->id])}}"
                   class="btn revert-btn inline-block">REVERT TO EDITABLE CAREPLAN FROM CCD/PATIENT DATA</a>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script src="{{asset('js/compiled/v-pdf-careplans.js')}}"></script>
@endsection