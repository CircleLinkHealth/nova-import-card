@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Import Eligible Patients CSV</div>

                    <div class="panel-body">
                        @include('partials.importEligiblePatientsCsv')
                    </div>
                </div>
            </div>

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
                    <div class="panel-heading">Import Eligible Patients Medical Records</div>

                    <div class="panel-body">
                        @include('partials.importEligiblePatientsMedicalRecords')
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

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Process Eligibility from CSV</div>

                    <div class="panel-body">
                        @include('partials.makeWelcomeCallsListUploadPanel')
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Process Eligibility from Google Drive</div>

                    <div class="panel-body">
                        @include('partials.makeWelcomeCallListGoogleDrivePanel')
                    </div>
                </div>
            </div>


            <div class="col-md-4">
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
    </div>
@endsection
