@extends('provider.layouts.onboarding')

@section('title', 'Create locations')

@section('instructions', "Almost done! Let's <u>setup locations</u>, then your relevant staff.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step2 {
                color: #039be5 !important;
            }
        </style>
    </head>

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
                                NEW LOCATION
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

                        <div class="row" v-if="index == 0 || !sameEHRLogin">
                            <h6>
                                Please provide login information for your EHR system.
                            </h6>

                            @include('provider.partials.mdl.form.text.textfield', [
                                'name' => 'locations[@{{index}}][ehr_login]',
                                'label' => 'EHR Login',
                                'class' => 'col s6',
                                'attributes' => [
                                    'v-model' => 'loc.ehrLogin',
                                    'required' => 'required',
                                    ':disabled' => 'sameEHRLogin && index > 0'
                                ]
                            ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                'name' => 'locations[@{{index}}][ehr_password]',
                                'label' => 'EHR Password',
                                'class' => 'col s6',
                                'type' => 'password',
                                'attributes' => [
                                    'autocomplete' => 'new-password',
                                    'required' => 'required',
                                    ':disabled' => 'sameEHRLogin && index > 0'
                                ]
                            ])

                            <p class="right-align">
                                @include('provider.partials.mdl.form.checkbox', [
                                   'label' => 'Same for all locations?',
                                   'name' => 'same-ehr-login',
                                   'value' => '1',
                                   'attributes' => [
                                        'v-model' => 'sameEHRLogin',
                                    ]
                               ])
                            </p>
                        </div>

                        <div class="row" v-if="index == 0 || !sameClinicalIssuesContact">
                            <h6>
                                Who should be notified for patient clinical issues?
                            </h6>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'billing-provider',
                                    'label' => 'Patient\'s Billing / Main provider.',
                                    'name' => 'clinical-contact',
                                    'value' => 'billing-provider',
                                    'attributes' => [
                                        'v-model' => 'patientClinicalIssuesContact',
                                        'required' => 'required',
                                    ]
                                ])
                            </div>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'instead-of-billing-provider',
                                    'label' => 'Someone else instead of the billing provider.',
                                    'name' => 'clinical-contact',
                                    'value' => 'instead-of-billing-provider',
                                    'attributes' => [
                                        'v-model' => 'patientClinicalIssuesContact',
                                        'required' => 'required',
                                    ]
                                ])
                                <transition>
                                    <div v-if="patientClinicalIssuesContact == 'instead-of-billing-provider' ? true : false"
                                         name="custom-classes-transition"
                                         enter-active-class="animated tada"
                                         leave-active-class="animated bounceOutRight"
                                         mode="in-out">
                                        @include('provider.partials.clinical-issues-contact')
                                    </div>
                                </transition>
                            </div>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'in-addition-to-billing-provider',
                                    'label' => 'Someone else in addition to the billing provider.',
                                    'name' => 'clinical-contact',
                                    'value' => 'in-addition-to-billing-provider',
                                    'attributes' => [
                                        'v-model' => 'patientClinicalIssuesContact',
                                        'required' => 'required',
                                    ]
                                ])
                                <transition>
                                    <div v-if="patientClinicalIssuesContact == 'in-addition-to-billing-provider' ? true : false"
                                         name="fade" mode="in-out">
                                        @include('provider.partials.clinical-issues-contact')
                                    </div>
                                </transition>
                            </div>


                            <div class="right-align">
                                @include('provider.partials.mdl.form.checkbox', [
                                   'label' => 'Same for all locations?',
                                   'name' => 'same-clinical-contact',
                                   'value' => '1',
                                   'attributes' => [
                                        'v-model' => 'sameClinicalIssuesContact'
                                   ]
                               ])
                            </div>
                        </div>

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
            <div v-on:click="addLocation" class="btn waves-effect waves-light blue accent-1">
                Add Location
                <i class="material-icons right">add</i>
            </div>
        </div>

        <div class="row">
            <button class="btn blue waves-effect waves-light col s12" id="submit">
                Next
            </button>
        </div>

        {!! Form::close() !!}

    </div>
@endsection


@section('scripts')
    <script src="/js/create-locations.js"></script>
@endsection

