@extends('provider.layouts.onboarding')

@section('title', 'Create locations')

@section('instructions', "Create locations blah blah. Title: create-locations Step 3/4")

@section('module')

    <div class="row">

        @include('errors.errors')

        {!! Form::open([
            'url' => route('post.onboarding.store.locations'),
            'method' => 'post',
            'id' => 'create-practice',
        ]) !!}

        <ul class="collapsible" data-collapsible="accordion">
            @for($i = 1; $numberOfLocations >= $i; $i++)
                <li id="location-{{ $i }}">
                    <div class="collapsible-header active">
                        Location #{{ $i }}
                    </div>

                    <div class="collapsible-body" style="padding: 5%;">

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][name]", 'label' => 'Name ', 'class' =>'col s6'])
                            {!! Form::select("locations[$i][timezone]", timezones(), 'America/New_York', ['class' => 'col s6 input-field']) !!}
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][address_line_1]", 'label' => 'Address Line 1 ', 'class' =>'col s8'])

                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][address_line_2]", 'label' => 'Address Line 2 ', 'class' =>'col s4'])
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][city]", 'label' => 'City ', 'class' =>'col s6'])

                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][state]", 'label' => 'State ', 'class' =>'col s6'])
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][postal_code]", 'label' => 'Postal Code ', 'class' =>'col s6'])
                            @include('provider.partials.mdl.form.text.textfield', [ 'name' => "locations[$i][phone]", 'label' => 'Phone ', 'class' =>'col s6'])
                        </div>

                        <input type="hidden" name="locations[{{$i}}][practice_id]" value="{{$practiceId}}">
                    </div>
                </li>
            @endfor
        </ul>

        <button class="btn blue waves-effect waves-light col s12" id="submit">
            Create location(s)
        </button>

        {!! Form::close() !!}

    </div>



@endsection