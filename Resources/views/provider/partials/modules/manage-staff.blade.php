<meta name="submit-url" content="{{$postUrl}}">

<div v-cloak id="create-staff-component" v-on:click="isValidated(index)">

    <div v-if="showErrorBanner" class="row">
        <div class="card-panel red lighten-5 red-text text-darken-4">
            <b>Whoops! We found some errors with your Input. Please correct them and click Next</b>.
        </div>
    </div>

    {!! Form::open([
        'url' => route('post.onboarding.store.staff', ['practiceSlug' => $practiceSlug]),
        'method' => 'post',
        'id' => 'create-staff',
    ]) !!}

    <div class="row">
        <div v-on:click="submitForm('{{$postUrl}}')"
             class="btn blue waves-effect waves-light col s12"
             v-bind:class="{disabled: !formCompleted}"
             id="store-staff">
            {{$submitLabel}}
        </div>
    </div>

    <div class="row">
        <ul id="users" class="collapsible" data-collapsible="accordion">
            <li v-for="(newUser, index) in newUsers" v-bind:id="'user-'+index" v-on:click="isValidated(index)">
                <div class="collapsible-header" v-bind:class="{ active: (index == newUsers.length - 1) }">
                    <div class="col s8">
                            <span v-if="newUser.first_name || newUser.last_name">
                                @{{newUser.first_name.toUpperCase()}} @{{newUser.last_name.toUpperCase()}}
                                | @{{ newUser.role_id > 0 ? rolesMap[newUser.role_id].display_name : 'No role selected'}}
                            </span>
                        <span v-else>
                                NEW USER
                            </span>
                    </div>

                    <div class="col s4 right-align">
                        <div v-if="isValidated(index)">
                            <span class="green-text">Valid Data</span>
                        </div>
                        <div v-else>
                            <span v-if="newUser.errorCount > 0" class="red-text"><u>Invalid Input</u></span>
                            <span v-else class="red-text">Incomplete</span>
                        </div>
                    </div>
                </div>

                <div class="collapsible-body" style="padding: 5%;">

                    <div class="row">
                        @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "users[@{{index}}][first_name]",
                                'label' => 'First Name',
                                'class' =>'col s6',
                                'attributes' => [
                                    'v-model' => 'newUser.first_name',
                                    'v-bind:value' => 'newUser.first_name',
                                    'required' => 'required',
                                    'v-on:change' => 'isValidated(index)',
                                    'v-on:invalid' => 'isValidated(index)',
                                    'v-on:keyup' => 'isValidated(index)',
                                    'v-on:click' => 'isValidated(index)',
                                ]
                            ])

                        @include('provider.partials.mdl.form.text.textfield', [
                                'name' => "users[@{{index}}][last_name]",
                                'label' => 'Last Name',
                                'class' =>'col s6',
                                'attributes' => [
                                    'v-model' => 'newUser.last_name',
                                    'v-bind:value' => 'newUser.last_name',
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
                            'name' => "users[@{{index}}][email]",
                            'label' => 'Email',
                            'class' => 'col s6',
                            'type' => 'email',
                            'attributes' => [
                                'v-model' => 'newUser.email',
                                'v-bind:value' => 'newUser.email',
                                'required' => 'required',
                                'v-on:change' => 'isValidated(index)',
                                'v-on:invalid' => 'isValidated(index)',
                                'v-on:keyup' => 'isValidated(index)',
                                'v-on:click' => 'isValidated(index)',
                            ],
                            'data_error' => 'Email needs to contain an @.',
                        ])

                        <div class="input-field col s6 validate">
                            <material-select v-bind:selected="newUser.role_id" v-model="newUser.role_id" required>
                                <option value="" disabled selected></option>
                                <option v-for="role in roles" v-bind:value="role.id">@{{role.display_name}}</option>
                            </material-select>
                            <label>Role</label>
                        </div>
                    </div>

                    <div class="row">
                        @include('provider.partials.mdl.form.text.textfield', [
                            'name' => 'users[@{{index}}][phone_number]',
                            'label' => 'Phone',
                            'class' => 'col s6',
                            'attributes' => [
                                'v-model' => 'newUser.phone_number',
                                'v-bind:value' => 'newUser.phone_number',
                                'required' => 'required',
                                'minlength' => 10,
                                'maxlength' => 12,
                                'v-on:change' => 'isValidated(index)',
                                'v-on:invalid' => 'isValidated(index)',
                                'v-on:keyup' => 'isValidated(index)',
                                'v-on:click' => 'isValidated(index)',
                                'pattern' => '\d{3}[\-]\d{3}[\-]\d{4}'
                            ],
                            'data_error' => 'Phone number must have this format: xxx-xxx-xxxx'
                        ])

                        @include('provider.partials.mdl.form.text.textfield', [
                           'name' => 'users[@{{index}}][phone_extension]',
                           'label' => 'Extension',
                           'class' => 'col s3',
                           'attributes' => [
                               'v-model' => 'newUser.phone_extension',
                               'v-bind:value' => 'newUser.phone_extension',
                               'v-on:change' => 'isValidated(index)',
                               'v-on:invalid' => 'isValidated(index)',
                               'v-on:keyup' => 'isValidated(index)',
                               'v-on:click' => 'isValidated(index)',
                           ],
                       ])

                        <div class="input-field col s3">
                            <material-select id="phones" v-bind:selected="newUser.phone_type" v-model="newUser.phone_type"
                                             required v-on:change="isValidated(index)"
                                             name="users[@{{index}}][phone_type]">
                                <option value="" disabled selected></option>
                                <option v-for="(type, index) in phoneTypes" :value="index">@{{ type }}</option>
                            </material-select>
                            <label>Phone Type</label>
                        </div>
                    </div>

                    <div class="row">
                        @include('provider.partials.mdl.form.text.textfield', [
                           'name' => "users[@{{index}}][emr_direct_address]",
                           'label' => 'EMR Direct Address',
                           'type' => 'email',
                           'class' =>'col s6',
                           'value' => '@{{loc.emr_direct_address}}',
                               'attributes' => [
                                   'v-model' => 'newUser.emr_direct_address',
                                   'required' => 'required',
                                   'v-on:change' => 'isValidated(index)',
                                   'v-on:invalid' => 'isValidated(index)',
                                   'v-on:keyup' => 'isValidated(index)',
                                   'v-on:click' => 'isValidated(index)',
                               ],
                           'data_error' => 'EMR Direct Address must include an @.'
                       ])
                    </div>

                    <div class="row">
                        <div class="col s5 offset-s8">
                            <div class="left-align">
                                @include('provider.partials.mdl.form.checkbox', [
                                    'label' => 'Grant admin rights',
                                    'name' => 'users[@{{index}}][grand_admin_rights]',
                                    'value' => '1',
                                    'class' => 'col s12',
                                    'attributes' => [
                                        'v-model' => 'newUser.grantAdminRights'
                                    ],
                                ])
                            </div>

                            <div class="left-align">
                                @include('provider.partials.mdl.form.checkbox', [
                                    'label' => 'Send billing reports',
                                    'name' => 'users[@{{index}}][send_billing_reports]',
                                    'value' => '1',
                                    'class' => 'col s12',
                                    'attributes' => [
                                        'v-model' => 'newUser.sendBillingReports'
                                    ],
                                ])
                            </div>
                        </div>
                    </div>

                    {{--Forward Clinical Issues Alerts--}}
                    <div v-show="newUser.role_id == 5" class="row">
                        <div class="input-field col s12">

                            <h6>Whom should we notify for clinical issues regarding provider’s patients?</h6>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'billing-provider-@{{index}}',
                                    'label' => 'Provider',
                                    'name' => 'users[@{{index}}][forward_alerts_to][who]',
                                    'value' => 'billing_provider',
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_alerts_to.who',
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
                                    'id' => 'instead-of-provider-@{{index}}',
                                    'label' => 'Someone else instead of provider.',
                                    'name' => 'users[@{{index}}][forward_alerts_to][who]',
                                    'value' => \CircleLinkHealth\Customer\Entities\User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER,
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_alerts_to.who',
                                        'required' => 'required',
                                        'v-on:change' => 'isValidated(index)',
                                        'v-on:invalid' => 'isValidated(index)',
                                        'v-on:keyup' => 'isValidated(index)',
                                        'v-on:click' => 'isValidated(index)',
                                    ]
                                ])

                                <transition>
                                    <div v-show="newUser.forward_alerts_to.who == '{{\CircleLinkHealth\Customer\Entities\User::FORWARD_ALERTS_INSTEAD_OF_PROVIDER}}'">
                                        <br>
                                        <div class="col s12">
                                            @include('provider.partials.clinicalIssuesNotifyUser')
                                        </div>
                                    </div>
                                </transition>
                            </div>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'in-addition-@{{index}}',
                                    'label' => 'Notify others in addition to provider.',
                                    'name' => 'users[@{{index}}][forward_alerts_to][who]',
                                    'value' => \CircleLinkHealth\Customer\Entities\User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER,
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_alerts_to.who',
                                        'required' => 'required',
                                        'v-on:change' => 'isValidated(index)',
                                        'v-on:invalid' => 'isValidated(index)',
                                        'v-on:keyup' => 'isValidated(index)',
                                        'v-on:click' => 'isValidated(index)',
                                    ]
                                ])
                                <transition>
                                    <div v-show="newUser.forward_alerts_to.who == '{{\CircleLinkHealth\Customer\Entities\User::FORWARD_ALERTS_IN_ADDITION_TO_PROVIDER}}'">
                                        <br>
                                        <div class="col s12">
                                            @include('provider.partials.clinicalIssuesNotifyUser')
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <br>

                    {{--Forward CarePlan Approval reminders--}}
                    <div v-show="newUser.role_id == 5" class="row">
                        <div class="input-field col s12">

                            <h6>Whom should we notify for approval of care plans regarding provider’s patients?</h6>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'cp-emails-billing-provider-@{{index}}',
                                    'label' => 'Provider',
                                    'name' => 'users[@{{index}}][forward_careplan_approval_emails_to][who]',
                                    'value' => 'billing_provider',
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_careplan_approval_emails_to.who',
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
                                    'id' => 'cp-emails-instead-of-provider-@{{index}}',
                                    'label' => 'Someone else instead of provider.',
                                    'name' => 'users[@{{index}}][forward_careplan_approval_emails_to][who]',
                                    'value' => \CircleLinkHealth\Customer\Entities\User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER,
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_careplan_approval_emails_to.who',
                                        'required' => 'required',
                                        'v-on:change' => 'isValidated(index)',
                                        'v-on:invalid' => 'isValidated(index)',
                                        'v-on:keyup' => 'isValidated(index)',
                                        'v-on:click' => 'isValidated(index)',
                                    ]
                                ])

                                <transition>
                                    <div v-show="newUser.forward_careplan_approval_emails_to.who == '{{\CircleLinkHealth\Customer\Entities\User::FORWARD_CAREPLAN_APPROVAL_EMAILS_INSTEAD_OF_PROVIDER}}'">
                                        <br>
                                        <div class="col s12">
                                            @include('provider.partials.forwardCareplanApprovalEmails')
                                        </div>
                                    </div>
                                </transition>
                            </div>

                            <div>
                                @include('provider.partials.mdl.form.radio', [
                                    'id' => 'cp-emails-in-addition-@{{index}}',
                                    'label' => 'Notify others in addition to provider.',
                                    'name' => 'users[@{{index}}][forward_careplan_approval_emails_to][who]',
                                    'value' => \CircleLinkHealth\Customer\Entities\User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER,
                                    'attributes' => [
                                        'v-model' => 'newUser.forward_careplan_approval_emails_to.who',
                                        'required' => 'required',
                                        'v-on:change' => 'isValidated(index)',
                                        'v-on:invalid' => 'isValidated(index)',
                                        'v-on:keyup' => 'isValidated(index)',
                                        'v-on:click' => 'isValidated(index)',
                                    ]
                                ])
                                <transition>
                                    <div v-show="newUser.forward_careplan_approval_emails_to.who == '{{\CircleLinkHealth\Customer\Entities\User::FORWARD_CAREPLAN_APPROVAL_EMAILS_IN_ADDITION_TO_PROVIDER}}'">
                                        <br>
                                        <div class="col s12">
                                            @include('provider.partials.forwardCareplanApprovalEmails')
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="input-field col s12">
                            <material-select v-bind:selected="newUser.locations" v-model="newUser.locations" required multiple>
                                <option v-for="location in locations" :value="location.id"
                                        selected>@{{location.name}}</option>
                            </material-select>
                            <label>Locations</label>
                        </div>
                    </div>

                    <input v-if="newUser.id" type="hidden" name="users[@{{index}}][id]"
                           value="@{{ newUser.id }}">

                    <div class="row" v-if="newUsers.length > 0">
                        <a class="waves-effect waves-teal btn-flat red lighten-3 white-text"
                           v-on:click="deleteUser(index)"><i
                                    class="material-icons left">delete</i>Trash @{{ newUser.first_name }} @{{ newUser.last_name }}
                        </a>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <div class="row right-align">
        <div v-on:click="addUser" class="btn waves-effect waves-light blue accent-1">
            Add User
            <i class="material-icons right">add</i>
        </div>
    </div>

    <div class="row">
        <div v-on:click="submitForm('{{$postUrl}}')"
             class="btn blue waves-effect waves-light col s12"
             v-bind:class="{disabled: !formCompleted}"
             id="store-staff">
            {{$submitLabel}}
        </div>
    </div>

    {!! Form::close() !!}
</div>


@push('scripts')
    <script src="/js/create-staff.js"></script>
@endpush
