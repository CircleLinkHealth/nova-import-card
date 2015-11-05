@extends('app')

@section('content')
<link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<div class="container container--menu">
    <div class="row row-centered">
        <div class="col-sm-12">
            <ul class="menu-item-list">

                <li class="menu-item">
                    <a href="#">
                        <div class="icon-container column-centered">
                            <i class="icon--find-patient--big icon--menu"></i>
                        </div>
                        <div>
                            <p class="text-medium-big text--menu text-serif">Select a Patient<BR><BR></p>
                        </div>
                    </a>
                </li>

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

 -->			</ul>

        </div>
    </div>
</div>
@stop