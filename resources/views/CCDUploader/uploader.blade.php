@extends('app')

@section('content')
<div id="uploads"></div>
<div class="dropzone" id="dropzone">Drop one or more XML CCD record here</div>

<script src="{{ asset('/js/ccd/bluebutton.js') }}"></script>
<script src="{{ asset('/js/ccd/ccdParseUpload.js') }}"></script>
@endsection
