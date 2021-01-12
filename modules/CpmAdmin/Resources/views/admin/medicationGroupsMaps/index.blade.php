@extends('cpm-admin::partials.adminUI')

@section('content')
    <meta name="medication.groups.maps.store" content="{{route('medication-groups-maps.store',  [], false)}}">
    <meta name="medication.groups.maps.destroy" content="{{route('medication-groups-maps.destroy', [''], false)}}">
    <cpm-medication-groups-maps-settings medication-groups-maps="{{$maps}}" medication-groups="{{$medicationGroups}}"></cpm-medication-groups-maps-settings>
@endsection
