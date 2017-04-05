@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">

        <div class="col-md-12">
            <div class="col-sm-8">
                <h1>Welcome, {{ $user->fullName }}</h1>
            </div>
            <div class="col-sm-4">
                <div class="pull-right" style="margin:20px;">
                    <a href="{{ URL::route('patients.dashboard', array()) }}" class="btn btn-info"
                       style="margin-left:10px;"><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">CCD Viewer</div>

                    <div class="panel-body">
                        @include('CCDViewer.create-old-viewer')
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Generate Welcome Calls List</div>

                    <div class="panel-body">
                        @include('partials.makeWelcomeCallsListUploadPanel')
                    </div>
                </div>
            </div>


            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Train Medical Record Importing Algorithm</div>

                    <div class="panel-body">
                        @include('partials.importerTrainer')
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Grab Athena CCDs</div>

                    <div class="panel-body">
                        @include('partials.getAthenaCcdsById')
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Send Sample note Fax</div>

                    <div class="panel-body">
                        <form action="/send-sample-fax" method="POST">
                            {{csrf_field()}}
                            <input type="text" pattern="^\+?[1-9]\d{1,14}$" name="fax_number" placeholder="+12223334444"
                                   required>
                            <input type="submit" value="send">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Stats:</div>

                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                            <td></td>
                            <td></td>
                            <td></td>
                            </thead>
                            <tbody>
                            <tr>
                                <td><strong>Total Programs</strong></td>
                                <td>{{ $stats['totalPrograms'] }}</td>
                                <td><a class="btn btn-primary btn pull-right"
                                       href="{{ URL::route('admin.programs.index', array()) }}"><i
                                                class="icon--home--white"></i> View All Programs</a></td>
                            </tr>
                            <tr>
                                <td><strong>Total Users</strong></td>
                                <td>{{ $stats['totalUsers'] }}</td>
                                <td><a class="btn btn-primary btn pull-right"
                                       href="{{ URL::route('admin.users.index', array()) }}"><i
                                                class="icon--home--white"></i> view All Users</a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Roles:</div>

                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                            <td></td>
                            <td></td>
                            <td></td>
                            </thead>
                            <tbody>
                            @foreach($roleStats as $statName => $statCount)
                                <tr>
                                    <td><strong>Total {{ $statName }}</strong></td>
                                    <td>{{ $statCount }}</td>
                                    <td><a class="btn btn-primary btn pull-right"
                                           href="{{ URL::route('admin.users.index', array('filterRole' => $statName)) }}"><i
                                                    class="icon--home--white"></i> {{ $statName }}</a></td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
