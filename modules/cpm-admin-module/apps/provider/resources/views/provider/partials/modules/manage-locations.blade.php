<meta name="submit-url" content="{{$postUrl}}">

<div v-cloak id="create-locations-component" class="row">

    <div v-if="showErrorBanner" class="row">
        <div class="card-panel red lighten-5 red-text text-darken-4">
            <b>Whoops! We found some errors with your Input. Please correct them and click Next</b>.
        </div>
    </div>

    {!! Form::open([
        'url' => $postUrl,
        'method' => 'post',
        'id' => 'create-practice',
    ]) !!}

    <div class="row">
        <div v-on:click="submitForm('{{$postUrl}}')"
             class="btn blue waves-effect waves-light col s12" id="submit"
             v-bind:class="{disabled: !formCompleted}">
            {{$submitLabel}}
        </div>
    </div>

    <div class="row">
        <ul class="collapsible" data-collapsible="accordion">
            <li v-for="(loc, index) in newLocations" v-bind:id="'location-'+index" v-on:click="isValidated(index)">
                <div class="collapsible-header" v-bind:class="{ active: index == newLocations.length - 1 }">
                    <div class="col s8">
                            <span v-if="loc.name">
                                @{{loc.name.toUpperCase()}}
                            </span>
                        <span v-else>
                                NEW LOCATION
                            </span>
                    </div>
                    <div class="col s4 right-align">
                        <div v-if="isValidated(index)">
                            <span class="green-text">Valid Data</span>
                        </div>
                        <div v-else>
                            <span v-if="loc.errorCount > 0" class="red-text"><u>Invalid Input</u></span>
                            <span v-else class="red-text">Incomplete</span>
                        </div>
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
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])


                        <material-select v-model="loc.timezone" class="col s6 input-field">
                            <option v-for="option in timezoneOptions" :value="option.value"
                                    v-text="option.name"></option>
                        </material-select>

                    </div>

                    <div class="row">
                        @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "locations[@{{index}}][address_line_1]",
                                'label' => 'Address Line 1 ',
                                'class' =>'col s8',
                                'value' => '@{{loc.address_line_1}}',
                                'attributes' => [
                                    'v-model' => 'loc.address_line_1',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])

                        @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "locations[@{{index}}][address_line_2]",
                                'label' => 'Address Line 2 ',
                                'class' =>'col s4',
                                'value' => '@{{loc.address_line_2}}',
                                'attributes' => [
                                    'v-model' => 'loc.address_line_2',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
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
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])

                        @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "locations[@{{index}}][state]",
                                'label' => 'State ',
                                'class' =>'col s6',
                                'value' => '@{{loc.state}}',
                                'attributes' => [
                                    'v-model' => 'loc.state',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
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
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                        ])

                        @include('provider.partials.mdl.form.text.textfield', [
                            'name' => "locations[@{{index}}][phone]",
                            'label' => 'Phone',
                            'class' =>'col s6',
                            'value' => '@{{loc.phone}}',
                                'attributes' => [
                                    'v-model' => 'loc.phone',
                                    'required' => 'required',
                                    'pattern' => '\d{3}[\-]\d{3}[\-]\d{4}',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ],
                            'data_error' => 'Phone number must have this format: xxx-xxx-xxxx'
                        ])
                    </div>

                    <div class="row">
                        @include('provider.partials.mdl.form.text.textfield', [
                           'name' => "locations[@{{index}}][fax]",
                           'label' => 'Fax',
                           'class' =>'col s6',
                           'value' => '@{{loc.fax}}',
                               'attributes' => [
                                   'v-model' => 'loc.fax',
                                   'required' => 'required',
                                   'pattern' => '\d{3}[\-]\d{3}[\-]\d{4}',
                                   'v-on:change' => 'isValidated(index)',
                                   'v-on:invalid' => 'isValidated(index)',
                                   'v-on:keyup' => 'isValidated(index)',
                                   'v-on:click' => 'isValidated(index)',
                               ],
                           'data_error' => 'Fax number must have this format: xxx-xxx-xxxx'
                       ])

                        @include('provider.partials.mdl.form.text.textfield', [
                           'name' => "locations[@{{index}}][emr_direct_address]",
                           'label' => 'EMR Direct Address',
                           'class' =>'col s6',
                           'type' => 'email',
                           'value' => '@{{loc.emr_direct_address}}',
                               'attributes' => [
                                   'v-model' => 'loc.emr_direct_address',
                                   'required' => 'required',

                                   'v-on:change' => 'isValidated(index)',
                                   'v-on:invalid' => 'isValidated(index)',
                                   'v-on:keyup' => 'isValidated(index)',
                                   'v-on:click' => 'isValidated(index)',
                               ],
                           'data_error' => 'EMR Direct Address must include an @.'
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
                                'v-model' => 'loc.ehr_login',
                                'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                            ]
                        ])

                        @include('provider.partials.mdl.form.text.textfield', [
                            'name' => 'locations[@{{index}}][ehr_password]',
                            'label' => 'EHR Password',
                            'class' => 'col s6',
                            'type' => 'text',
                            'attributes' => [
                                'v-model' => 'loc.ehr_password',
                                'autocomplete' => 'new-password',
                                'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                            ]
                        ])

                        <p class="right-align" v-if="index == 0">
                            @include('provider.partials.mdl.form.checkbox', [
                               'name' => 'sameEHRLogin',
                               'label' => 'Same for all locations?',
                               'value' => '1',
                               'attributes' => [
                                    'v-model' => 'sameEHRLogin',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
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
                                'id' => 'billing-provider-@{{index}}',
                                'label' => 'Patient\'s Billing / Main provider.',
                                'name' => 'locations[@{{index}}][clinical_contact][type]',
                                'value' => 'billing_provider',
                                'attributes' => [
                                    'v-model' => 'loc.clinical_contact.type',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])
                        </div>

                        <div>
                            @include('provider.partials.mdl.form.radio', [
                                'id' => 'instead-of-billing-provider-@{{index}}',
                                'label' => 'Notify others instead of the billing provider.',
                                'name' => 'locations[@{{index}}][clinical_contact][type]',
                                'value' => 'instead_of_billing_provider',
                                'attributes' => [
                                    'v-model' => 'loc.clinical_contact.type',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])
                            <transition name="fade">
                                <div v-if="loc.clinical_contact.type == 'instead_of_billing_provider' ? true : false"
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
                                'id' => 'in-addition-to-billing-provider-@{{index}}',
                                'label' => 'Notify others in addition to the billing provider.',
                                'name' => 'locations[@{{index}}][clinical_contact][type]',
                                'value' => 'in_addition_to_billing_provider',
                                'attributes' => [
                                    'v-model' => 'loc.clinical_contact.type',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])
                            <transition name="fade">
                                <div v-if="loc.clinical_contact.type == 'in_addition_to_billing_provider' ? true : false"
                                     name="fade" mode="in-out">
                                    @include('provider.partials.clinical-issues-contact')
                                </div>
                            </transition>
                        </div>


                        <div class="right-align" v-if="index == 0">
                            @include('provider.partials.mdl.form.checkbox', [
                               'label' => 'Same for all locations?',
                               'name' => 'locations[@{{index}}][same_clinical_contact]',
                               'value' => '1',
                               'attributes' => [
                                    'v-model' => 'sameClinicalIssuesContact',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
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
        <div v-on:click="submitForm('{{$postUrl}}')"
             class="btn blue waves-effect waves-light col s12" id="submit"
             v-bind:class="{disabled: !formCompleted}">
            {{$submitLabel}}
        </div>
    </div>

    {!! Form::close() !!}

</div>

@push('scripts')
    <script src="/js/create-locations.js"></script>
@endpush
