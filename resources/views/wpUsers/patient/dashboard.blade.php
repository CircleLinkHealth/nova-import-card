@extends('partials.providerUI')

@section('content')
    <div class="container container--menu">
        <div class="row row-centered">
            <div class="col-sm-12">
                <ul class="" style="margin:0;padding:0;">
                    <li class="menu-item">
                        <a href="{{ URL::route('patients.select', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--find-patient--big icon--menu"></i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Select a Patient<BR><BR><br></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ URL::route('patients.listing', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--list-patient--big icon--menu">
                                    <div class="notification btn-warning">{{ $pendingApprovals }}</div>
                                </i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Patient List<br><span style="color:red;font-style:italic;font-size:85%;" class="text-thin">{{ $pendingApprovals }} Pending<br>Approvals</span><br></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ URL::route('patients.demographics.show', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--add-patient--big icon--menu"></i>
                            </div>
                            <div class="">
                                <p class="text-medium-big text--menu text-serif">Add a Patient<BR><BR><BR></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ URL::route('patients.demographics.show', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--alerts--big icon--menu">
                                    <div class="notification btn-warning">-</div>
                                </i>
                            </div>
                            <div class="icon-container column-centered">
                                <p class="text-medium-big text--menu text-serif">My Alerts & &nbsp;&nbsp;<br> Tasks<BR><BR></p>
                            </div>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
@stop