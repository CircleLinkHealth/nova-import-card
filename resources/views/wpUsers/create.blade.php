@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#togglePrograms").click(function (event) {
                event.preventDefault();
                $("#programs").toggle();
                return false;
            });

            $(function () {
                $("#programsCheckAll").click(function () {
                    $(".programs").prop("checked", true);
                    return false;
                });

                $("#programsUncheckAll").click(function () {
                    $(".programs").prop("checked", false);
                    return false;
                });
            });
        });
    </script>
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
                            {!! Form::open(array('url' => URL::route('admin.users.store'), 'class' => 'form-horizontal')) !!}
                        </div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active"><a href="#program" aria-controls="program" role="tab"
                                                                      data-toggle="tab">Practice</a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">
                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('username', 'Login:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('username', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('email', 'email:') !!}</div>
                                        <div class="col-xs-10">{!! Form::text('email', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('password', 'Password:') !!}</div>
                                        <div class="col-xs-4">{!! Form::password('password', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-2">{!! Form::label('password_confirm', 'Confirm Password:') !!}</div>
                                        <div class="col-xs-4">{!! Form::password('password_confirm', ['class' => 'form-control']) !!}</div>
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
                                        <div class="col-xs-2">{!! Form::label('user_status', 'User Status:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('user_status', array('0' => '0', '1' => '1'), 1, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                        <div class="col-xs-2"></div>
                                        <div class="col-xs-4"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('ccm_status', 'CCM Status:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('ccm_status', array('paused' => 'paused', 'enrolled' => 'enrolled', 'withdrawn' => 'withdrawn'), '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
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



                                <h2><a data-toggle="collapse" data-target="#programCollapse" class="">Programs</a></h2>

                                <div id="programCollapse" class="collapse in" style="background:#888;padding:20px;">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2">{!! Form::label('program_id', 'Primary Practice:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('program_id', $wpBlogs, '', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                            <div class="col-xs-2"><strong>Practice Config:</strong><br/>Auto attach to
                                                new programs
                                            </div>
                                            <div class="col-xs-4">
                                                <br />
                                                <input id="auto_attach_programs" name="auto_attach_programs" value="1" type="checkbox">
                                            </div>
                                        </div>
                                    </div>


                                    <a class="btn btn-info panel-title" href="#" id="togglePrograms"><strong>Toggle Programs list</strong></a><br /><br />
                                    <div id="programs" style="display:none;">
                                        <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button> |
                                        <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>
                                        @foreach( $wpBlogs as $wpBlogId => $domain )
                                            <div class="row" id="program_{{ $wpBlogId }}" style="border-bottom:1px solid #000;">
                                                <div class="col-sm-2">
                                                    <div class="text-right">
                                                    {!! Form::checkbox('programs[]', $wpBlogId, [], ['style' => '', 'class' => 'programs']) !!}
                                                    </div>
                                                </div>
                                                <div class="col-sm-10">{!! Form::label('Value', 'Practice: '.$domain, array('class' => '')) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <h2>Location (for API Users)</h2>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2">{!! Form::label('id', 'Location:') !!}</div>
                                        <div class="col-xs-10">{!! Form::select('location_id', ['default' => 'Attach a location to API users'] + $locations, 'default', ['class' => 'form-control select-picker', '', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.users.index', array()) }}" class="btn btn-danger">Cancel</a>
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