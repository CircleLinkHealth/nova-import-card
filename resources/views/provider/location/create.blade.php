@extends('provider.layouts.dashboard')

@section('title', 'Locations')

@section('module')

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">

        <main class="mdl-layout__content mdl-cell--4-col">

            {!! Form::open(['url' => route('post.store.location'), 'method' => 'post']) !!}

            @foreach($locations as $location)
                @foreach($location as $key => $value)
                    @include('partials.form.text.textfield', [ 'name' => $key, 'label' => ucfirst($key), 'value' => $value ])

                    <div class="mdl-layout-spacer"></div>
                @endforeach
            @endforeach

            <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
                Update Practice
            </button>

            {!! Form::close() !!}


        </main>

    </div>

@endsection