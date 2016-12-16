@extends('partials.providerUI')

@section('title', 'Dashboard')
@section('activity', '')


@section('content')
    <div class="container container--menu">
        <div class="row row-centered">
            <div class="col-sm-12">
                <ul class="
                " style="margin:0;padding:0;">


                    <li class="menu-item">
                        <a id="select-patient" href="{{ URL::route('patients.search', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--find-patient--big icon--menu"></i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Select a Patient<BR><BR><br></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a id="patient-list" href="{{ URL::route('patients.listing', array()) }}">
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
                        <a id="add-patient" href="{{ URL::route('patients.demographics.show', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--add-patient--big icon--menu"></i>
                            </div>
                            <div class="">
                                <p class="text-medium-big text--menu text-serif">Add a Patient<BR><BR><BR></p>
                            </div>
                        </a>
                    </li>

                    @if(auth()->user()->hasRole(['administrator', 'care-center']))
                    <li class="menu-item">
                        <a id="patient-list" href="{{ URL::route('patientCallList.index', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--phone-call--big icon--menu"></i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Scheduled Calls<BR><BR><br></p>
                            </div>
                        </a>
                    </li>
                    @endif

                    {{--<li class="menu-item">--}}
                        {{--<a id="my-alerts" href="{{ URL::route('patients.demographics.show', array()) }}">--}}
                            {{--<div class="icon-container column-centered">--}}
                                {{--<i class="icon--alerts--big icon--menu">--}}
                                    {{--<div class="notification btn-warning">-</div>--}}
                                {{--</i>--}}
                            {{--</div>--}}
                            {{--<div class="icon-container column-centered">--}}
                                {{--<span class="glyphicon glyphicon-envelope" aria-hidden="true"--}}
                                      {{--style="height: 16px; width: 22px; font-size: 17px; top: 4px">--}}
                                {{--<p class="text-medium-big text--menu text-serif">Notes Sent to  &nbsp;&nbsp;<br>--}}
                                    {{--Provider<BR><BR></p>--}}
                                {{--</span>--}}
                            {{--</div>--}}
                        {{--</a>--}}
                    {{--</li>--}}

                </ul>
            </div>
        </div>

        @if( auth()->user()->can(['ccd-import']) )
            <div class="col-sm-12 text-center">
                <a href="{{ route('import.ccd') }}" class="btn btn-green btn-next inline-block submitFormBtn">
                    Import CCDs
                </a>
            </div>
            @endif

    </div>
@stop