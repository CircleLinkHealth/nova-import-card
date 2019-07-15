@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="main-form-title col-lg-12">
            <h1>
                Patient List
            </h1>
        </div>
        <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 overflow-scroll">
            <patient-list ref="patientList" :debug="@json(!isProductionEnv())"></patient-list>
        </div>
    </div>
</div>
@endsection
