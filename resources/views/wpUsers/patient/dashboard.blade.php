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

			</ul>

            <div class="row row-centered">
                <div class="col-sm-12">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th colspan="3">Pending Approvals</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(count($pendingApprovals) > 0) {
                            foreach ($pendingApprovals as $user) {
                            ?>
                                <tr>
                                    <td>{{ $user->fullName }}</td>
                                    <td>{{ $user->user_registered }}</td>
                                    <td>{{ $user->meta()->where('meta_key', '=', 'careplan_status')->first()->meta_value }}</td>
                                    <td><a class="btn btn-primary" href="">Approve Patient</a></td>
                                </tr>
                            <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@stop