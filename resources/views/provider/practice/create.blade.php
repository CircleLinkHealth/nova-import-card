@extends('provider.layouts.dashboard')

@section('title', 'Manage Practice')

@section('module')

    @include('errors.errors')

    <div class="container">
        <div class="row">
            {!! Form::open(['url' => route('provider.dashboard.store.practice', ['practiceSlug' => $practiceSlug]), 'method' => 'post', 'class' => 'col s12']) !!}


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

                <div class="col s6"></div>

                <div class="input-field col s12">
                    @include('provider.partials.mdl.form.checkbox', [
                        'label' => 'Auto Approve Care Plans',
                        'name' => 'auto_approve_careplans',
                        'value' => '1',
                        'class' => 'col s12',
                    ])
                </div>

                <div class="input-field col s12">
                    @include('provider.partials.mdl.form.checkbox', [
                        'label' => 'Send Alerts',
                        'name' => 'send_alerts',
                        'value' => '1',
                        'class' => 'col s12',
                        'attributes' => [
                            'checked' => 'checked',
                        ]
                    ])
                </div>
            </div>


            <button class="btn blue waves-effect waves-light col s12"
                    id="update-practice"
                    onclick="Materialize.toast('{{$practice->display_name}} was successfully updated.', 4000)">
                Update Practice
            </button>

            {!! Form::close() !!}

        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('select').select2();
    </script>
@endsection