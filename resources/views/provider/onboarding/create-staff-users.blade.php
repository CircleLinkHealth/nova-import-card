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
                        <div class="col s11">
                            <span v-if="newUser.first_name || newUser.last_name">
                                @{{newUser.first_name | uppercase}} @{{newUser.last_name | uppercase}}
                                | @{{newUser.role.name}}
                            </span>
                            <span v-else>
                                NEW USER
                            </span>
                        </div>
                    </div>

                    <div class="collapsible-body" style="padding: 5%;">

                        <div class="row">
                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "users[@{{index}}][first_name]",
                                    'label' => 'First Name',
                                    'class' =>'col s6',
                                    'value' => '@{{newUser.first_name}}',
                                    'attributes' => [
                                        'v-model' => 'newUser.first_name',
                                        'required' => 'required'
                                    ]
                                ])

                            @include('provider.partials.mdl.form.text.textfield', [
                                    'name' => "users[@{{index}}][last_name]",
                                    'label' => 'Last Name',
                                    'class' =>'col s6',
                                    'value' => '@{{newUser.last_name}}',
                                    'attributes' => [
                                        'v-model' => 'newUser.last_name',
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
                                'value' => '@{{newUser.email}}',
                                    'attributes' => [
                                        'v-model' => 'newUser.email',
                                        'required' => 'required'
                                    ]
                            ])

                            <div class="input-field col s6">
                                <select id="roles" v-model="newUser.role">
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

                        <div class="right-align">
                            @include('provider.partials.mdl.form.checkbox', [
                                'label' => 'Grant admin rights',
                                'name' => 'users[@{{index}}][grand_admin_rights]',
                                'value' => '1',
                                'class' => 'col s12',
                            ])
                        </div>

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
