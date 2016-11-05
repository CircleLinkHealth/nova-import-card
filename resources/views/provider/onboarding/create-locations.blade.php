@extends('provider.layouts.onboarding')

@section('title', 'Create locations')

@section('instructions', "Create locations blah blah. Title: create-locations")

<head>
    <style>
        {{--Hack to make this page scrollable--}}
        .v-center {
            display: block !important;
        }
    </style>
</head>

@section('module')
    {{$numberOfLocations}}
    @include('errors.errors')

    {!! Form::open([
        'url' => route('post.onboarding.store.locations'),
        'method' => 'post',
        'id' => 'create-practice',
    ]) !!}

    @for($i = 1; $numberOfLocations >= $i; $i++)

        <h6>locations {{ $i }}</h6>

        <div class="mdl-cell mdl-cell--12-col">
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][name]", 'label' => 'Name ', 'class' =>'mdl-cell--12-col '])
        </div>

        <div class="mdl-cell mdl-cell--12-col">
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][address_line_1]", 'label' => 'Address Line 1 ', 'class' =>'mdl-cell--12-col'])
        </div>

        <div class="mdl-cell mdl-cell--12-col">
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][address_line_2]", 'label' => 'Address Line 2 ', 'class' =>'mdl-cell--12-col'])
        </div>

        <div class="mdl-cell mdl-cell--12-col">
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][city]", 'label' => 'City ', 'class' =>'mdl-cell--6-col'])
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][state]", 'label' => 'State ', 'class' =>'mdl-cell--6-col'])
        </div>

        <div class="mdl-cell mdl-cell--12-col">
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][postal_code]", 'label' => 'Postal Code ', 'class' =>'mdl-cell--6-col'])
            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][phone]", 'label' => 'Phone ', 'class' =>'mdl-cell--6-col'])
        </div>

    @endfor

    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col"
            id="submit">
        Create location(s)
    </button>

    {!! Form::close() !!}

@endsection