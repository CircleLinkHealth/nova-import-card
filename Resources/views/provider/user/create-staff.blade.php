@extends('provider.layouts.dashboard')

@section('title', 'Manage Staff Members')

@section('module')
    <manage-practice-users
            :practice-settings="{{json_encode($practice->cpmSettings(), JSON_HEX_QUOT)}}"></manage-practice-users>
@endsection