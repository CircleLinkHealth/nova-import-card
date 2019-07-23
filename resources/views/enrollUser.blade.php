@extends('layouts.app')
@section('content')
    <enroll-user :patient-name="{{json_encode($patientName)}}"></enroll-user>
@endsection
