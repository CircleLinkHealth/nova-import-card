@extends('partials.providerUI')

@section('title', 'Schedule Task')
@section('activity', 'Schedule Task')

@section('content')
    <add-action-modal ref="addActionModal" patient-id="{{$patientId}}" practice-id="{{$practiceId}}"></add-action-modal>

@endsection
