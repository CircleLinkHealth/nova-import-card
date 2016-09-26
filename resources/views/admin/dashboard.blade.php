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
                @include('partials.calls.uploadCsv')
            </div>

            <div class="col-md-4">
                @include('partials.uploadGeneralCommentCsv')
            </div>
        </div>

        <div class="row">

        </div>

    </div>


@endsection
