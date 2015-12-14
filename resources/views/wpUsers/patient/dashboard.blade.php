@extends('partials.providerUI')

@section('content')
<div class="row" style="margin-top:60px;">
    <div class="main-form-container col-lg-8 col-lg-offset-2">
        <div class="row">
            <div class="main-form-title col-lg-12">
                Patient Overview
            </div>
            <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                <ul>
                    <li class="menu-item">
                        <a href="#">
                            <div class="icon-container column-centered">
                                <i class="icon--find-patient--big icon--menu"></i>
                            </div>
                            <div>
                                <p class="text-medium-big text--menu text-serif">Patient List<BR><BR></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ URL::route('patients.demographics.show', array()) }}">
                            <div class="icon-container column-centered">
                                <i class="icon--add-patient--big icon--menu"></i>
                            </div>
                            <div class="">
                                <p class="text-medium-big text--menu text-serif">Add a Patient<BR><BR></p>
                            </div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="#">
                            <div class="icon-container column-centered">
                                <i class="icon--alerts--big icon--menu">
                                    <div class="notification btn-warning">99</div>
                                </i>
                            </div>
                            <div class="icon-container column-centered">
                                <p class="text-medium-big text--menu text-serif">My Alerts & &nbsp;&nbsp;<br> Tasks</p>
                            </div>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div>
@stop