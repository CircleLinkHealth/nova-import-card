@extends('provider.layouts.dashboard')

@section('title', 'Practice')

@section('module')

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">

        <main class="mdl-layout__content mdl-cell--4-col">

            {!! Form::open(['url' => route('post.store.practice'), 'method' => 'post']) !!}

            @include('provider.partials.mdl.form.text.textfield', [
                'name' => 'name',
                'label' =>
                'Name',
                'value' => $practice->display_name,
                'class' => 'mdl-cell--12-col',
            ])

            @include('provider.partials.mdl.form.text.textfield', [
                'name' => 'description',
                'label' =>
                'Description',
                'value' => $practice->description,
                'class' => 'mdl-cell--12-col',
            ])

            <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col"
                    id="update-practice">
                Update Practice
            </button>

            {!! Form::close() !!}


        </main>

    </div>

@endsection