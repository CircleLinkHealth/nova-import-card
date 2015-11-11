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
                            <li role="presentation"><a href="#userconfig" aria-controls="userconfig" role="tab" data-toggle="tab">User Config</a></li>
                            <li role="presentation"><a href="#usercareteam" aria-controls="usercareteam" role="tab" data-toggle="tab">Care Team</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">
                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_login', 'Login:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('user_login', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('user_email', 'user_email:') !!}</div>
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
                                        <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('first_name', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('last_name', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                        <div class="col-xs-4">{!! Form::text('display_name', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('user_status', 'User Status:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('user_status', array('0' => '0', '1' => '1'), 1, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('ccm_enabled', 'CCM Enabled:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('ccm_enabled', array('false' => 'false', 'true' => 'true'), '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('careplan_status', 'Careplan Status:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('careplan_status', array('draft' => 'draft', 'qa_approved' => 'qa_approved', 'provider_approved' => 'provider_approved'), '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                </div>

                                <h2>Role</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                        <div class="col-xs-10">{!! Form::select('role', $roles, '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                    </div>
                                </div>

                                <h2>Primary Program</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('program_id', 'Primary Blog:') !!}</div>
                                        <div class="col-xs-10">{!! Form::select('program_id', $wpBlogs, '', ['class' => 'form-control select-picker', '', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>

                                <h2>Programs:</h2>
                                <div id="programs">
                                    @foreach( $wpBlogs as $wpBlogId => $domain )
                                        <div class="form-group role" id="program_{{ $wpBlogId }}">
                                            <div class="col-sm-1">
                                                {!! Form::checkbox('programs[]', $wpBlogId, [], ['style' => '']) !!}
                                            </div>
                                            <div class="col-sm-11">{!! Form::label('Value', 'Program: '.$domain, array('class' => '')) !!}</div>
                                        </div>
                                    @endforeach
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