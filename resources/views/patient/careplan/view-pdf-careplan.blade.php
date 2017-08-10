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
    <script src="{{asset('js/v-pdf-careplans.js')}}"></script>
@endsection