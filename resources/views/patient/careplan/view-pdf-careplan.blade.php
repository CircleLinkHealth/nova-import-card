@extends('partials.providerUI')

@section('title', 'Care Plan View/Print')
@section('activity', 'Care Plan View/Print')


@section('content')
    <div class="container">
        <pdf-careplans></pdf-careplans>
    </div>
@stop