@extends('partials.providerUI')

@section('content')

    <div class="container container--menu">
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
                            <h5>Please make your schedule by creating as many "windows" as you'd like.
                                <br><br><b>NOTE:</b> Windows become locked and cannot be edited after midnight of the
                                preceding Wednesday.</h5>
                        </div>
                        <div class="row-centered">
                            <div class="col-md-12">
                                @include('partials.care-center.work-schedule-slot.create')
                            </div>
                        </div>

                        <br><br><br><br>

                        <div class="row">
                            <hr class="col-md-12">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                @include('partials.care-center.work-schedule-slot.index')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

