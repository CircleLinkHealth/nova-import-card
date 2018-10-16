@extends('provider.layouts.dashboard')

@section('title', 'Manage Practice')

@section('module')

    @include('errors.errors')

    <div class="container">
        {!! Form::open(['url' => route('provider.dashboard.store.practice', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12', 'id' => 'edit-practice-form']) !!}

        <div class="row">
            <div class="input-field col s12">
                <input id="name" name="name" type="text" class="validate" value="{{$practice->display_name}}"
                       required disabled>
                <label for="name" data-error="required" data-success="">Name</label>
            </div>

            <div class="input-field col s12">
                <input id="federal_tax_id" type="text" class="validate" value="{{$practice->federal_tax_id}}"
                       name="federal_tax_id">
                <label for="federal_tax_id" data-error="required" data-success="">Federal tax ID</label>
            </div>
        </div>

        <div class="row">
            <div class="input-field col s6">
                <label for="lead" class="active" data-error="required">Practice Lead</label>
                <div style="height: 15px;"></div>
                <select id="lead" name="lead_id"
                        class="validate" required>

                    @if(!$practice->user_id)
                        <option value="0">None</option>
                    @endif

                    @foreach($staff as $user)
                        <option value="{{$user['id']}}" @if($user['id']==$practice->user_id){{'selected'}}@endif>{{$user['first_name']}} {{$user['last_name']}}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-field col s6">

                <label class="active" for="primary_location" data-error="required">Primary Location</label>
                <div style="height: 15px;"></div>


                <select id="primary_location" name="primary_location" class="validate" required>
                    @foreach($locations as $location)
                        <option value="{{$location['id']}}"
                                @if($location['is_primary']) selected @endif>{{$location['name']}}</option>
                    @endforeach
                </select>

            </div>
        </div>

        @if (auth()->user()->hasRole('administrator'))
            <div class="row">
                <div class="input-field col s6">
                    <input id="bill_to_name" name="bill_to_name" type="text" class="validate"
                           value="{{$practice->bill_to_name}}">
                    <label for="bill_to_name" data-success="">Bill To Name</label>
                </div>

                <div class="input-field col s3">
                    <input id="clh_pppm" type="number" class="validate" value="{{$practice->clh_pppm}}" name="clh_pppm">
                    <label for="clh_pppm" data-success="">CPM Price</label>
                </div>

                <div class="input-field col s3">
                    <input id="term_days" type="number" class="validate" value="{{$practice->term_days}}"
                           name="term_days">
                    <label for="term_days" data-success="">Term (Days)</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s4">
                    <input name="is_active" type="checkbox" id="is_active"
                           value="1" @if(!!$practice->active){{'checked'}}@endif>
                    <label for="is_active">Is Active</label>
                </div>
            </div>
        @endif


        <button class="btn blue waves-effect waves-light col s12"
                id="update-practice"
                form="edit-practice-form"
                onclick="Materialize.toast('{{$practice->display_name}} was successfully updated.', 4000)">
            Update Practice
        </button>

        {!! Form::close() !!}

    </div>
@endsection

@push('scripts')
    <script src="{{mix('/js/materialize.min.js')}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('select').material_select();
        });
    </script>
@endpush