@extends('partials.providerUI')

@section('content')
    <div class="container" style="padding-top: 3%;">
        <div class="row">
            <div class="col-md-12">
                <ccd-upload ref="ccdUpload"></ccd-upload>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <ccd-viewer ref="ccdViewer"></ccd-viewer>
            </div>
        </div>
    </div>
@endsection
