@extends('cpm-admin::provider.layouts.dashboard')

@section('title', 'Manage Practice')

@section('module')
    @include('core::partials.errors.errors')

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
                <span class="prefix">+1</span>
                <input id="outgoing_phone_number" type="tel"
                       name="outgoing_phone_number"
                       value="{{$practice->outgoing_phone_number}}">
                <label for="outgoing_phone_number">Outgoing Phone Number</label>
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


                <select id="primary_location" name="primary_location" class="validate" required data-error="required">
                    @forelse($locations as $location)
                        <option value="{{$location['id']}}"
                                @if($location['is_primary']) selected @endif>{{$location['name']}}</option>
                    @empty
                        <option value="0">No locations found</option>
                    @endforelse
                </select>

            </div>
        </div>

        @if (auth()->user()->isAdmin())
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
                <div class="input-field col s6">
                    <input name="is_active" type="checkbox" id="is_active"
                           value="1" @if(!!$practice->active){{'checked'}}@endif>
                    <label for="is_active">Is Active</label>
                </div>
                <div class="input-field col s6">
                    <input name="is_demo" type="checkbox" id="is_demo"
                           value="1" @if(!!$practice->is_demo){{'checked'}}@endif>
                    <label for="is_demo">Is Demo</label>
                </div>
            </div>

            <div class="row" style="padding-top: 5%;">
                <div class="input-field col s6">
                    <label for="default_user_scope" class="active" data-error="required">Staff can view patients from</label>
                    <div style="height: 15px;"></div>
                    <select id="default_user_scope" name="default_user_scope"
                            class="validate" required>
                        @foreach($userScopes as $scopeId => $scopeDescription)
                            <option value="{{$scopeId}}" @if($scopeId==$practice->default_user_scope){{'selected'}}@endif>{{$scopeDescription}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <br/>

        <button class="btn blue waves-effect waves-light col s12"
                id="update-practice"
                form="edit-practice-form">
            Update Practice
        </button>

        {!! Form::close() !!}

    </div>
@endsection

@push('scripts')
    <script src="{{asset('/js/materialize.min.js')}}"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    <script src="https://unpkg.com/libphonenumber-js/bundle/libphonenumber-js.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('select').material_select();

            @if(\Session::has('message'))
            Materialize.toast('{{\Session::get('message')}}', 4000)
                    @endif

            const submit = $('#update-practice');
            const el = $('#outgoing_phone_number');

            // we don't want to force anyone to set the phone number if its already invalid
            // someone might visit this page to edit some other data
            if (el.val().length > 0) {
                toggleValidInvalidClass(el, validateNumber(el.val()));
            }

            el.keyup(function (e) {
                const isValid = validateNumber(el.val());
                toggleValidInvalidClass(el, isValid);
                toggleDisabledSubmitButton(submit, isValid);
            });

            function validateNumber(text) {
                try {
                    const number = libphonenumber.parsePhoneNumber(text, 'US');
                    return number.isValid() && number.country === 'US';
                }
                catch (e) {
                    return false;
                }
            }

            function toggleValidInvalidClass(el, valid) {
                if (valid) {
                    el.addClass('valid');
                    el.removeClass('invalid');
                }
                else {
                    el.addClass('invalid');
                    el.removeClass('valid');
                }
            }

            function toggleDisabledSubmitButton(button, valid) {
                button.prop('disabled', !valid);
            }
        });
    </script>
@endpush