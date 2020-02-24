@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Import Eligible Patients Using Eligible Patient Id (enrollee id)</div>

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
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default" data-step="1"
                     data-intro="This box helps you import CCDs for patients we have already processed for eligibility in CPM. It only applies for batches of CCDs we have already processed.">
                    <div class="panel-heading">Import Eligible Patients Medical Records <span class="pull-right"><a
                                    href="javascript:void(0);"
                                    onclick="javascript:introJs().setOption('showProgress', true).start();"><span
                                        class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a></span>
                    </div>

                    <div class="panel-body">
                        @include('partials.importEligiblePatientsMedicalRecords')
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">Pull Eligible Patients from Athena</div>
                    <div class="panel-body">
                        <form action="{{ route('pull.athena.enrollees') }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="form-group">
                                <article>Select Practice</article>
                                <select name="practice_id" class="col-sm-12 form-control">
                                    @foreach(CircleLinkHealth\Customer\Entities\Practice::whereEhrId(2)->get() as $practice)
                                        <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <article>From:</article>
                                <input id="from" type="date" name="from"
                                       value="{{Carbon\Carbon::today()->subDay()->toDateString()}}"
                                       max="{{Carbon\Carbon::today()->subDay()->toDateString()}}" required
                                       class="form-control">
                                <article>To:</article>
                                <input id="to" type="date" name="to" value="{{Carbon\Carbon::today()->toDateString()}}"
                                       max="{{Carbon\Carbon::today()->toDateString()}}" required class="form-control">
                            </div>

                            <input type="submit" class="btn btn-primary" value="Pull" name="submit">

                        </form>
                        @if(Session::has('pullMsg'))
                            <strong>{{Session::get('pullMsg')}}</strong>
                        @endif
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
                <div class="panel panel-default">
                    <div class="panel-heading">Demo UPG G0506 Flow</div>
                    <div class="panel-body">
                        @if (\Session::has('upg0506-command-success'))
                            <div class="alert alert-success">
                                <span>{{\Session::get('upg0506-command-success')}}</span>
                            </div>
                        @endif
                        <a class="btn btn-m btn-primary" href="{{route('upg0506.demo', ['type'=>'pdf'])}}">Send PDF</a>
                        <a class="btn btn-m btn-info" href="{{route('upg0506.demo', ['type'=> 'ccd'])}}">Send CCD</a>
                        <a class="btn btn-m btn-danger" href="{{route('upg0506.demo', ['type' => 'delete'])}}">Delete Test Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection