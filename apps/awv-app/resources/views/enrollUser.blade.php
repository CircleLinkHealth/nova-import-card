@extends('layouts.app')
@section('content')
    <enroll-user :patient-id="{{json_encode($patientId)}}" :patient-name="{{json_encode($patientName)}}"></enroll-user>
@endsection
