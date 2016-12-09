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

    <div id="create-staff-component">

        @include('provider.partials.errors.validation')

        {!! Form::open([
            'url' => route('post.onboarding.store.staff'),
            'method' => 'post',
            'id' => 'create-staff',
        ]) !!}

        <div class="row">
            <ul class="collapsible" data-collapsible="accordion">
                <li v-for="(index, newUser) in newUsers" id="user-@{{index}}">
                    <div class="collapsible-header" v-bind:class="{ active: index == newUsers.length - 1 }">
                        <div class="col s10">
                            <span v-if="newUser.first_name || newUser.last_name">
                                @{{newUser.first_name | uppercase}} @{{newUser.last_name | uppercase}}
                                | @{{newUser.role.name}}
                            </span>
                            <span v-else>
                                NEW USER
                            </span>
                        </div>
                        <div class="col s1">
                            <span v-if="!newUser.first_name
                            || !newUser.last_name
                            || !newUser.email
                            || !newUser.phone.number
                            || !newUser.role.id
                            || !newUser.phone.id"
                                  class="red-text">incomplete</span>
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
                                        'required' => 'required'
                                    ]
                                ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "users[@{{index}}][last_name]",
                                    'label' => 'Last Name',
                                    'class' =>'col s6',
                                    'attributes' => [
                                        'v-model' => 'newUser.last_name',
                                        'v-bind:value' => 'newUser.last_name',
                                        'required' => 'required'
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
                                    'required' => 'required'
                                ]
                            ])

                            <div class="input-field col s6">
                                <select id="roles" v-model="newUser.role" name="users[@{{index}}][role_id] required">
                                    <option v-bind:value="{id:0, name:'No Role Selected'}" disabled selected></option>
                                    <option v-bind:value="{id:1, name:'Medical Assistant'}">Medical Assistant</option>
                                    <option v-bind:value="{id:1, name:'Office Staff'}">Office Staff</option>
                                    <option v-bind:value="{id:1, name:'Practice Lead'}">Practice Lead</option>
                                    <option v-bind:value="{id:1, name:'Provider (PCP)'}">Provider (PCP)</option>
                                    <option v-bind:value="{id:1, name:'Registered Nurse'}">Registered Nurse</option>
                                    <option v-bind:value="{id:1, name:'Specialist'}">Specialist</option>
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
                                    'v-model' => 'newUser.phone.number',
                                    'v-bind:value' => 'newUser.phone.number',
                                    'required' => 'required'
                                ]
                            ])

                            <div class="input-field col s6">
                                <select id="phones" v-model="newUser.phone">
                                    <option v-bind:value="{id:0, name:'No Role Selected'}" disabled selected></option>
                                    <option v-bind:value="{id:1, name:'Medical Assistant'}">Cell</option>
                                    <option v-bind:value="{id:1, name:'Medical Assistant'}">Home</option>
                                    <option v-bind:value="{id:1, name:'Office Staff'}">Work</option>
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
                                    ])
                                </div>

                                <div class="left-align">
                                    @include('provider.partials.mdl.form.checkbox', [
                                        'label' => 'Send billing reports',
                                        'name' => 'users[@{{index}}][send_billing_reports]',
                                        'value' => '1',
                                        'class' => 'col s12',
                                    ])
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="input-field col s12">
                                <select multiple>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" selected>{{$location->name}}</option>
                                    @endforeach
                                </select>
                                <label>Locations</label>
                            </div>
                        </div>

                        <input v-if="newUser.id" type="hidden" name="users[@{{index}}][user_id]"
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
            <button class="btn blue waves-effect waves-light col s12"
                    id="store-staff">
                Next
            </button>
        </div>

        {!! Form::close() !!}
    </div>

@endsection

@section('scripts')
    <script src="/js/create-staff.js"></script>
@endsection
