@extends('provider.layouts.onboarding')

@section('title', 'Create locations')

@section('instructions', "Create locations. Click on Add Location to add locations, and save to continue blah blah. Title: create-locations Step 3/4")

@section('module')

    <div id="create-locations-component" class="row">

        @include('provider.partials.errors.validation')

        {!! Form::open([
            'url' => route('post.onboarding.store.locations'),
            'method' => 'post',
            'id' => 'create-practice',
        ]) !!}

        <div class="row">
            <ul class="collapsible" data-collapsible="accordion">
                <li v-for="(index, loc) in newLocations" id="location-@{{index}}">
                    <div class="collapsible-header" v-bind:class="{ active: index == newLocations.length - 1 }">
                        <div class="col s11">
                            <span v-if="loc.name">
                                @{{loc.name | uppercase}}
                            </span>
                            <span v-else>
                                LOCATION #@{{index}}
                            </span>
                        </div>
                    </div>

                    <div class="collapsible-body" style="padding: 5%;">

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "locations[@{{index}}][name]",
                                    'label' => 'Name',
                                    'class' =>'col s6',
                                    'value' => '@{{loc.name}}',
                                    'attributes' => [
                                        'v-model' => 'loc.name',
                                        'required' => 'required'
                                    ]
                                ])

                            @include('provider.partials.locations-dropdown', [
                                'name' => 'locations[0][timezone]'
                            ])
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "locations[@{{index}}][address_line_1]",
                                    'label' => 'Address Line 1 ',
                                    'class' =>'col s8',
                                    'value' => '@{{loc.address_line_1}}',
                                    'attributes' => [
                                        'v-model' => 'loc.address_line_1',
                                        'required' => 'required'

                                    ]
                                ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "locations[@{{index}}][address_line_2]",
                                    'label' => 'Address Line 2 ',
                                    'class' =>'col s4',
                                    'value' => '@{{loc.address_line_2}}',
                                    'attributes' => [
                                        'v-model' => 'loc.address_line_2',
                                        'required' => 'required'
                                    ]
                                ])
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "locations[@{{index}}][city]",
                                    'label' => 'City ',
                                    'class' =>'col s6',
                                    'value' => '@{{loc.city}}',
                                    'attributes' => [
                                        'v-model' => 'loc.city',
                                        'required' => 'required'
                                    ]
                                ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "locations[@{{index}}][state]",
                                    'label' => 'State ',
                                    'class' =>'col s6',
                                    'value' => '@{{loc.state}}',
                                    'attributes' => [
                                        'v-model' => 'loc.state',
                                        'required' => 'required'
                                    ]
                                ])
                        </div>

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "locations[@{{index}}][postal_code]",
                                'label' => 'Postal Code ',
                                'class' =>'col s6',
                                'value' => '@{{loc.postal_code}}',
                                    'attributes' => [
                                        'v-model' => 'loc.postal_code',
                                        'required' => 'required'
                                    ]
                            ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "locations[@{{index}}][phone]",
                                'label' => 'Phone ',
                                'class' =>'col s6',
                                'value' => '@{{loc.phone}}',
                                    'attributes' => [
                                        'v-model' => 'loc.phone',
                                        'required' => 'required'
                                    ]
                            ])
                        </div>

                        <input type="hidden" name="locations[@{{index}}][practice_id]" value="{{$practiceId}}">

                        <div class="row" v-if="newLocations.length > 1">
                            <a class="waves-effect waves-teal btn-flat red lighten-3 white-text"
                               v-on:click="deleteLocation(index)"><i
                                        class="material-icons left">delete</i>Trash @{{ loc.name }}</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <div class="row right-align">
            <div v-on:click="addLocation" class="btn waves-effect waves-light blue accent-1" type="submit"
                 name="action">
                Add Location
                <i class="material-icons right">send</i>
            </div>
        </div>

        <div class="row">
            <button class="btn blue waves-effect waves-light col s12" id="submit">
                Save location(s)
            </button>
        </div>

        {!! Form::close() !!}

    </div>

    <script src="/js/create-locations.js"></script>

@endsection

