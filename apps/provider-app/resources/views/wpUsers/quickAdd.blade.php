@extends('partials.adminUI')

@section('content')

@include('partials.patientAddForm',['headings' => $headings, 'items' => $items, 'days' => $days, 'providers' => $providers, 'offices' => $offices])

@stop