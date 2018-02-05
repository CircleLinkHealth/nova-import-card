@extends('provider.layouts.dashboard')

@section('title', 'Chargeable Services')

@section('module')


    @include('errors.materialize-errors')


    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.chargeable-services', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id'=>'practice-chargeable-services-form']) !!}

            <button type="submit"
                    form="practice-chargeable-services-form"
                    class="btn blue waves-effect waves-light col s4"
                    onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
                Update Preferences
            </button>



        <button type="submit"
                form="practice-chargeable-services-form"
                class="btn blue waves-effect waves-light col s4"
                onclick="Materialize.toast('{{$practice->display_name}} preferences was successfully updated.', 4000)">
            Update Preferences
        </button>

        {!! Form::close() !!}

    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('select').material_select();
        });
    </script>
@endpush