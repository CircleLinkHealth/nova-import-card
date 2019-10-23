@extends('partials.providerUI')

@section('title', 'Dashboard')
@section('activity', 'Dashboard')


@section('content')
    <div class="container container--menu">
        <div class="row row-centered">
            <div class="col-sm-12">
                @if(! isProductionEnv())
                    @include('patient.create-test-patients')
                @endif
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
                    <li class="menu-item">
                        <a id="patient-list" href="{{ route('patients.listing', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--list-patient--big icon--menu">
                                    <div class="notification btn-warning">{{ $pendingApprovals }}</div>
                                </i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Patient List<br><span
                                            style="color:red;font-style:italic;font-size:85%;" class="text-thin">{{ $pendingApprovals }}
                                        Pending<br>Approvals</span><br></p>
                            </div>
                        </a>
                    </li>

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
                    @if( auth()->user()->hasPermission(['ccd-import']) )
                        <li class="menu-item">
                            <a id="patient-list" href="{{ route('import.ccd.remix') }}">
                                <div class="icon-container column-centered">
                                    <i class="icon--menu" aria-hidden="true">
                                        <img src="{{mix('/img/icon--download.png')}}"
                                             style="
                                            max-width: 61px;
                                            position: absolute;
                                            left: 15px;
                                            bottom: 12px;">
                                    </i>
                                </div>
                                <div>
                                    <p class="text-medium-big text--menu text-serif">Import CCDs<BR><BR><br></p>
                                </div>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        @include('errors.errors')

        @if($nurse && auth()->user()->isNotSaas())
            @include('partials.care-center.dashboard-schedule', [$nurse])
        @endif

    </div>

    <div class="container-fluid">
        @if($showPatientsPendingApprovalBox)
            @include('partials.provider.patients-pending-approval')
        @endif
    </div>
@endsection