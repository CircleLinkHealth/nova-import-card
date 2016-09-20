@extends('provider.layouts.dashboard')

@section('title', 'Locations')

@section('module')

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">
        <main class="mdl-layout__content mdl-cell--12-col">

            @foreach($locations as $location)
                <section class="mdl-cell--4-col">
                    @include('provider.partials.location.show', $location)
                </section>
            @endforeach
        </main>
    </div>



@endsection