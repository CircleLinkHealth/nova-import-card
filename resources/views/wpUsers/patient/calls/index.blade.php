@extends('partials.providerUI')

@section('title', 'Call Patient Page')
@section('activity', 'Call Patient Page')

@section('content')
    @push('scripts')
        <script src="https://media.twiliocdn.com/sdk/js/client/v1.6/twilio.min.js"></script>
    @endpush

    <div class="row" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Call Dashboard
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    @if(!empty($patient->phoneNumbers))
                        <call-number v-bind:numbers="{{$patient->phoneNumbers->map(function($p) {return $p->number;} )}}">
                        </call-number>
                    @else
                        <p>No phone numbers found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection