@extends('layouts.app')

@section('content')
    <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 overflow-scroll">
        <patient-list
            ref="patientList"
            :debug="@json(!isProductionEnv())"
            ccd-importer-url="{{config('services.cpm.ccd_importer_url')}}"
            wellness-docs-url="{{config('services.cpm.wellness_docs_url')}}">
        </patient-list>
    </div>
@endsection
