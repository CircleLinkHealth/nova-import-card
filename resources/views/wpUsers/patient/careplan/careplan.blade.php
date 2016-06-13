<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@if($patient->careplanStatus == 'provider_approved')
    @section('title', 'Edit/Modify Care Plan')
@section('activity', 'Edit/Modify Care Plan')
@else
    @section('title', 'Initial Care Plan Setup')
@section('activity', 'Initial Care Plan Setup')
@endif

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    {!! Form::open(array('url' => URL::route('patient.careplan.store', array('patientId' => $patient->ID)), 'class' => '', 'id' => 'ucpForm')) !!}

    <div id="content" class="row">
        <div class="container">
            <section class="">
                <div class="row">
                    <div class="icon-container col-lg-12">
                        @if(isset($patient) && !$new_user )
                            @include('wpUsers.patient.careplan.nav')
                        @endif
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="main-form-container col-lg-8 col-lg-offset-2">
                        <div class="row">
                            <div class="main-form-title col-lg-12">
                                Edit Patient Care Plan
                            </div>
                            @include('partials.userheader')
                        </div>
                    </div>
                </div>

                <div class="row">
                    <input type="hidden" name="user_id" value="{{ $patient->ID }}">
                    <input type="hidden" name="program_id" value="{{ $patient->program_id }}">

                    @if($carePlan)
                        <input type="hidden" name="careplan_id" value="{{ $carePlan->id }}">

                        {{-- Call CPM Partials Here --}}

                        {{--This will render each section--}}
                        @foreach($sections as $sectionKey => $section)
                            @include('partials.cpm-models.section')
                        @endforeach

                    @else
                        <div class="row" style="margin:60px 0px;">
                            <div class="col-lg-8 col-lg-offset-2 text-center">
                                No careplan found for this patient<br/>
                            </div>
                        </div>
                    @endif

                </div>

                <input id="save" name="formSubmit" value="Save" tabindex="0" type="hidden">
                <div class="main-form-progress">
                    <div class="row row-centered">
                    </div>
                </div><!-- /main-form-progress -->


                <!--footer-->
                @include('wpUsers.patient.careplan.footer')
                <br/><br/>

            </section>
        </div>
    </div>

    {{--Added this to allow for testing, since submit is done via js--}}
    @if(app()->environment('testing'))
        {!! Form::submit('TestSubmit', ['id' => 'unit-test-submit']) !!}
    @endif

    {!! Form::close() !!}
























    <!-- navigation bar -->
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <a class="navbar-brand"><i class="glyphicon glyphicon-bullhorn"></i> Vue Events Bulletin Board</a>
        </div>
    </nav>

    <!-- main body of our application -->
    <div class="container" id="events">

        <!-- add an event form -->
        <div class="col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3>Add an Event</h3>
                </div>
                <div class="panel-body">

                    <div class="form-group">
                        <input class="form-control" placeholder="Event Name" v-model="event.name">
                    </div>

                    <div class="form-group">
                        <textarea class="form-control" placeholder="Event Description" v-model="event.description"></textarea>
                    </div>

                    <div class="form-group">
                        <input type="date" class="form-control" placeholder="Date" v-model="event.date">
                    </div>

                    <button class="btn btn-primary" v-on:click.stop.prevent="addEvent()">Submit</button>

                </div>
            </div>
        </div>

        <!-- show the events -->
        <div class="col-sm-6">
            <div class="list-group">
                <template v-for="eventitem in events">
                <div href="#" class="list-group-item" v-on:submit.prevent v-if="eventitem.name">
                    <h4 class="list-group-item-heading">
                        <i class="glyphicon glyphicon-bullhorn" v-if="eventitem.name"></i>
                        @{{ eventitem.name }}
                        <textarea v-model="eventitem.name" id="event-edit-@{{ $index }}" style="display:none;">@{{ eventitem.name }}</textarea>
                    </h4>

                    <h5>
                        <i class="glyphicon glyphicon-calendar" v-if="eventitem.date"></i>
                        @{{ eventitem.date }}
                    </h5>

                    <p class="list-group-item-text" v-if="eventitem.description">@{{ eventitem.description }}</p>
                    <button class="btn btn-xs btn-primary" v-if="eventitem.description" v-on:click.stop.prevent="deleteEvent($index, $event)" >Delete</button>
                    <button class="btn btn-xs btn-danger" v-if="eventitem.description" v-on:click.stop.prevent="editEvent($index, $event)">Edit</button>
                </div>
                </template>

            </div>
        </div>
    </div>









    <!-- JS -->
    <script src="/js/careplan.js"></script>


@stop