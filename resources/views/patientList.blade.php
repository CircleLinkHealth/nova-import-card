@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="main-form-title col-lg-12">
            Patient List
        </div>
        <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 overflow-scroll">
            <patient-list ref="patientList"></patient-list>
        </div>
    </div>
</div>
@endsection
