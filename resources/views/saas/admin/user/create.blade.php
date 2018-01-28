@extends('partials.providerUI')

@section('title', 'Create Internal User')

@section('content')

    @push('scripts')
    <script>
        $(document).ready(function () {
            let practices = $(".practices")

            practices.select2({closeOnSelect: false})

            //show selections in the order they were selected
            practices.on('select2:select', function (e) {
                var id = e.params.data.id;
                var option = $(e.target).children('[value=' + id + ']');
                option.detach();
                $(e.target).append(option).change();
            });
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .form-group {
            margin: 20px !important;
        }
    </style>
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        Add Internal User
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        @include('errors.errors')
                                    </div>
                                </div>

                                <div id="create-internal-user-form-container">
                                    {!! Form::open(['url' => route('saas-admin.users.store'), 'method' => 'post', 'class' => 'form-horizontal']) !!}

                                    <div role="tabpanel" class="tab-pane active" id="program">
                                        <h2>User Information</h2>
                                        <hr>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">{!! Form::label('username', 'Username') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">{!! Form::text('user[username]', $usernameField, ['class' => 'form-control', 'required' => true]) !!}</div>

                                                <div class="col-xs-2">{!! Form::label('email', 'Email') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">{!! Form::text('user[email]', $emailField, ['class' => 'form-control', 'required' => true]) !!}</div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">{!! Form::label('first_name', 'First Name') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">{!! Form::text('user[first_name]', $firstNameField, ['class' => 'form-control', 'required' => true]) !!}</div>

                                                <div class="col-xs-2">{!! Form::label('last_name', 'Last Name') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">{!! Form::text('user[last_name]', $lastNameField, ['class' => 'form-control', 'required' => true]) !!}</div>
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                        <h2>Access Rights</h2>
                                        <hr>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-2">{!! Form::label('practices', 'Practices') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">
                                                    {!! Form::select('practices', $practices, $practicesField, ['class' => 'practices dropdown Valid form-control', 'required'=>true, 'multiple'=>true]) !!}
                                                </div>

                                                <div class="col-xs-2">{!! Form::label('role', 'Role') !!} <span style="color: red;">*</span></div>
                                                <div class="col-xs-4">{!! Form::select('role', $roles, $roleField, ['class' => 'form-control select-picker']) !!}</div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="radio-inline">
                                                        <input id="auto_attach_programs" name="auto_attach_programs"
                                                               value="1" type="checkbox">
                                                        <label for="auto_attach_programs"><span> </span>Grant permission
                                                            to all practices</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" style="margin-top:50px;">
                                        <div class="col-sm-12">
                                            <div class="pull-right">
                                                {!! Form::submit('Create User', array('class' => 'btn btn-success')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection