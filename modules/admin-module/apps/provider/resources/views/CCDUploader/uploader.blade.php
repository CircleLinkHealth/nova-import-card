@extends('layouts.ccd-importer', ['currentVue' => 'ccd-uploader'])

@include('partials.importerHeader')

@section('content')
    <ccd-uploader></ccd-uploader>
@endsection
