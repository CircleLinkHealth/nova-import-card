@extends('partials.providerUI')

@section('content')

    <div class="container container--menu">
        <div class="row">

            @include('errors.errors')

            <div class="panel panel-info">
                <div class="panel-heading">When would you like to work?</div>

                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <h5>You can make your schedule by creating as many "Windows" as you like. Your Windows for
                                next week cannot be deleted after Wednesday at midnight.</h5>
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

