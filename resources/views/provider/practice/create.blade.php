@extends('provider.layouts.dashboard')

@section('title', 'Practice')

@section('module')

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">

        <main class="mdl-layout__content mdl-cell--4-col">

            {!! Form::open(['url' => route('post.store.practice'), 'method' => 'post']) !!}

            @include('partials.form.text.textfield', [ 'name' => 'name', 'label' => 'Name' ])

            <div class="mdl-layout-spacer"></div>

            @include('partials.form.text.textfield', [ 'name' => 'description', 'label' => 'Description' ])

            <div class="mdl-layout-spacer"></div>

            <div class="mdl-grid">
                <div class="mdl-cell--6-col">
                    @include('partials.form.text.textfield', [ 'name' => 'url', 'label' => 'URL' ])
                </div>
                <div class="mdl-cell--6-col">
                    <h6>.careplanmanager.com</h6>
                </div>
            </div>
            <div class="mdl-layout-spacer"></div>

            <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
                Update Practice
            </button>

            {!! Form::close() !!}


        </main>

    </div>

@endsection