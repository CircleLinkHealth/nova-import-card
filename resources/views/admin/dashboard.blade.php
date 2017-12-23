@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">

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
                    <div class="panel-heading">CCD Viewer</div>

                    <div class="panel-body">
                        @include('CCDViewer.create-old-viewer')
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
                    <div class="panel-heading">Generate Welcome Calls List</div>

                    <div class="panel-body">
                        @include('partials.makeWelcomeCallsListUploadPanel')
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Send Sample note via Fax</div>

                    <div class="panel-body">
                        <form action="/send-sample-fax" method="POST">
                            {{csrf_field()}}
                            <input type="text" name="fax_number" placeholder="+12223334444 or 111-111-1111"
                                   required>
                            <input type="submit" value="send">
                        </form>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">Send Sample note via Direct Mail</div>

                    <div class="panel-body">
                        <form action="/send-sample-direct-mail" method="POST">
                            {{csrf_field()}}
                            <input type="email" name="direct_address" placeholder="mail@direct.clh.com"
                                   required>
                            <input type="submit" value="send">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
