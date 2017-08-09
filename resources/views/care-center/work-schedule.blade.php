@extends('partials.providerUI')

@section('title', 'Work Schedule')

@section('content')
    <div class="container container--menu" id="show-nurse-work-schedule">

        <notifications></notifications>

        <div class="row">

            @include('errors.errors')

            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4>
                        When would you like to work?
                    </h4>
                </div>

                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <h5>Please input the actual hours you will work for each day of the week, AND the time range during which you will work.</h5>
                        </div>
                        <div class="row-centered">
                            <div class="col-md-12">
                                @include('partials.care-center.work-schedule-slot.create')
                            </div>
                        </div>

                        <br><br><br><br>

                        <div class="row">
                            <h5>Please enter dates you will not be working:</h5>
                        </div>
                        <div class="row-centered">
                            <div class="col-md-12">
                                @include('partials.care-center.holiday-schedule.create')
                            </div>
                        </div>

                        <br><br><br><br>

                        <div class="row">
                            <hr class="col-md-12">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <h3 class="minimum-padding">Your Schedule ({{$tzAbbr}})</h3>

                                        <span class="info minimum-padding"
                                               data-tooltip="You may edit your total hours by double clicking on any of the hours boxes (below the days of week) below. Press enter to save your changes.">
                                            <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        </span>

                                </div>
                                @include('partials.care-center.work-schedule-slot.index')
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <h3>Your Days Off</h3>
                                </div>
                                @include('partials.care-center.holiday-schedule.index')
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('scripts')
    <script src="{{asset('js/nurse-work-schedule.js')}}"></script>
@endsection
