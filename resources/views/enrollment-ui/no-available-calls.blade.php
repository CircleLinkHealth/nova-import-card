@extends('enrollment-ui.layout')

@section('title', 'Enrollment Calls')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col" style="margin-top: 20%">
                <div class="card horizontal">
                    <div class="card-image">
                        <img src="{{mix('/img/cookie.png')}}">
                    </div>
                    <div class="card-stacked">
                        <div class="card-content">
                            <h2 class="header" style="color: #47beab">Oops!</h2>
                            <p>You’re out of patients to call, please contact your administrator to request more calls.</p>
                            </br>
                            <p>In the meantime, enjoy this cookie.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@push('scripts')

@endpush

