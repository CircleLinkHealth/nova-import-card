@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New User</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Create User
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => '/users', 'class' => 'form-horizontal')) !!}
                        </div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active"><a href="#program" aria-controls="program" role="tab" data-toggle="tab">Program</a></li>
                            <li role="presentation"><a href="#roles" aria-controls="roles" role="tab" data-toggle="tab">Roles</a></li>
                            <li role="presentation"><a href="#userinfo" aria-controls="userinfo" role="tab" data-toggle="tab">User Info</a></li>
                            <li role="presentation"><a href="#userconfig" aria-controls="userconfig" role="tab" data-toggle="tab">User Config</a></li>
                            <li role="presentation"><a href="#usercareteam" aria-controls="usercareteam" role="tab" data-toggle="tab">Care Team</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">
                                <h2>Program</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('primary_blog', 'Primary Blog:') !!}</div>
                                        <div class="col-xs-10">{!! Form::select('primary_blog', $wpBlogs, '', ['class' => 'form-control select-picker', '', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>

                                <h2>New User System Info</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_login', 'Login:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('user_login', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_email', 'Email:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('user_email', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_pass', 'Password:') !!}</div>
                                        <div class="col-xs-4">{!! Form::password('user_pass', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('user_pass_confirm', 'Confirm Password:') !!}</div>
                                        <div class="col-xs-4">{!! Form::password('user_pass_confirm', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_nicename', 'Nice Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('user_nicename', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('display_name', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="roles">
                                <h2>Roles:</h2>
                                <div id="roles">
                                    @foreach( $roles as $role )
                                        <div class="form-group role" id="role_{{ $role }}">
                                            <div class="col-sm-1">
                                                {!! Form::checkbox('roles[]', $role->id, [], ['style' => '']) !!}
                                            </div>
                                            <div class="col-sm-11">{!! Form::label('Value', 'Role: '.$role->display_name, array('class' => '')) !!}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <br />
                                <br />
                                <br />

                                <h3>WP Role</h3>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('role', 'WP Role:') !!}</div>
                                        <div class="col-xs-10">{!! Form::select('role', $providers_arr, '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                </div>
                                <br />
                            </div>

                            <div role="tabpanel" class="tab-pane" id="userinfo">
                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('first_name', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('last_name', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('nickname', 'Nickname:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('nickname', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('description', 'Description:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('description', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane" id="userconfig">
                                <h2>User Config</h2>
                                Create user first
                            </div>

                            <div role="tabpanel" class="tab-pane" id="usercareteam">
                                <h2>Care Team</h2>
                                Create user first
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('users.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Create User', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop