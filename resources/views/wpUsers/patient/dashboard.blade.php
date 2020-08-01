@extends('partials.providerUI')

@section('title', 'Dashboard')
@section('activity', 'Dashboard')

<?php
$patientListDropdown = getPatientListDropdown(auth()->user());
$hasAwv              = in_array('awv', $patientListDropdown);
?>

@section('content')
    <div class="container container--menu">
        <div class="row row-centered">
            <div class="col-sm-12">
                <ul class="" style="margin:0;padding:0;">

                    {{--<li class="menu-item">--}}
                    {{--<a id="select-patient" href="{{ route('patients.search', array()) }}">--}}
                    {{--<div class="icon-container column-centered">--}}
                    {{--<i class="icon--find-patient--big icon--menu"></i>--}}
                    {{--</div>--}}
                    {{--<div>--}}
                    {{--<p class="text-medium-big text--menu text-serif">Select a Patient<BR><BR><br></p>--}}
                    {{--</div>--}}
                    {{--</a>--}}
                    {{--</li>--}}

                    @if(! auth()->user()->isCareCoach())
                        @if (config('services.awv.url', null) && $hasAwv)
                            <li class="menu-item">
                                <a href="{{ config('services.awv.url') . '/manage-patients' }}">
                                    <div class="icon-container column-centered">
                                        <i class="icon--list-patient--big icon--menu">
                                        </i>
                                    </div>
                                    <div>
                                        <p class="text-medium-big text--menu text-serif">
                                            Wellness Visit Patient List
                                            <br/>
                                            <br/>
                                        </p>
                                    </div>
                                </a>
                            </li>
                        @endif

                        <li class="menu-item">
                            <a id="patient-list" href="{{ route('patients.listing', array()) }}">
                                <div class="icon-container column-centered">
                                    <i class="icon--list-patient--big icon--menu">
                                        <div class="notification btn-warning">{{ $pendingApprovals }}</div>
                                    </i>
                                </div>
                                <div>
                                    <p class="text-medium-big text--menu text-serif">CCM Patient List<br><span
                                                style="color:red;font-style:italic;font-size:85%;" class="text-thin">{{ $pendingApprovals }}
                                        Pending<br>Approvals</span><br></p>
                                </div>
                            </a>
                        </li>
                    @endif

                    <li class="menu-item">
                        <a dusk="add-patient-btn" href="{{ route('patient.demographics.create') }}">
                            <div class="icon-container column-centered">
                                <i class="icon--add-patient--big icon--menu"></i>
                            </div>
                            <div class="">
                                <p class="text-medium-big text--menu text-serif">Add a Patient<BR><BR><BR></p>
                            </div>
                        </a>
                    </li>

                    @if(auth()->user()->hasPermission('has-schedule') && auth()->user()->isNotSaas())
                        <li class="menu-item">
                            <a id="patient-list" href="{{ route('patientCallList.index', array()) }}">
                                <div class="icon-container column-centered">
                                    <i class="icon--phone-call--big icon--menu"></i>
                                </div>
                                <div>
                                    <p class="text-medium-big text--menu text-serif">Scheduled Activities<BR><BR></p>
                                </div>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        @include('errors.errors')
    </div>

        @if($showPatientsPendingApprovalBox)
            <div class="container-fluid">
                @if($seesAutoApprovalButton)
                    <div class="row">
                        <div class="col-lg-11">
                            {{link_to_action('Patient\PatientController@autoQAApprove', 'Auto QA', ['userId' => auth()->id()], ['class' => 'btn btn-success pull-right'])}}
                        </div>
                    </div>
                @endif

                @include('partials.provider.patients-pending-approval')
            </div>

        @endif
@endsection
