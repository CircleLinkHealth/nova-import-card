@extends('provider.layouts.onboarding')

@section('title', 'Create Staff')

@section('instructions', "One more! <br>Let's <u>create your team</u>.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step3 {
                color: #039be5 !important;
            }
        </style>
    </head>

    <div id="create-staff-component" v-on:click="isValidated(index)">

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
            <ul id="users" class="collapsible" data-collapsible="accordion">
                <li v-for="(index, newUser) in newUsers" id="user-@{{index}}" v-on:click="isValidated(index)">
                    <div class="collapsible-header" v-bind:class="{ active: (index == newUsers.length - 1) }">
                        <div class="col s9">
                            <span v-if="newUser.first_name || newUser.last_name">
                                @{{newUser.first_name | uppercase}} @{{newUser.last_name | uppercase}}
                                | @{{ newUser.role_id > 0 ? rolesMap[newUser.role_id].display_name : 'No role selected'}}
                            </span>
                            <span v-else>
                                NEW USER
                            </span>
                        </div>

                        <div class="col s3 right-align">
                            <div v-if="newUser.validated && newUser.errorCount == 0">
                                <span class="green-text">Complete!</span>
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
                                <select v-select="newUser.role_id" required>
                                    <option value="" disabled selected></option>
                                    <option v-for="role in roles" v-bind:value="role.id">@{{role.display_name}}</option>
                                </select>
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

                            <div class="input-field col s6">
                                <select id="phones" v-select="newUser.phone_type" name="users[@{{index}}][phone_type]"
                                        required v-on:change="isValidated(index)">
                                    <option value="" disabled selected></option>
                                    <option v-for="(index, type) in phoneTypes" :value="index">@{{ type }}</option>
                                </select>
                                <label>Phone Type</label>
                            </div>
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
                                            'v-model' => 'newUser.grandAdminRights'
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

                        <div class="row">
                            <div class="input-field col s12">
                                <select v-select="newUser.locations" required multiple>
                                    <option v-for="location in locations" :value="location.id"
                                            selected>@{{location.name}}</option>
                                </select>
                                <label>Locations</label>
                            </div>
                        </div>

                        <input v-if="newUser.id" type="hidden" name="users[@{{index}}][id]"
                               value="@{{ newUser.id }}">

                        <div class="row" v-if="newUsers.length > 1">
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
            <div v-on:click="submitForm('{{route('post.onboarding.store.staff', ['practiceSlug' => $practiceSlug])}}')"
                 class="btn blue waves-effect waves-light col s12"
                 v-bind:class="{disabled: !formCompleted}"
                 id="store-staff">
                Next
            </div>
        </div>

        {!! Form::close() !!}
    </div>

@endsection

@section('scripts')
    <script src="/js/create-staff.js"></script>
@endsection
