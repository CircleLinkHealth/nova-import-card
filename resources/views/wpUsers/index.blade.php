@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-2">
                        <h1>Users</h1>
                    </div>
                    @if(Entrust::can('users-create'))
                        <div class="col-sm-10">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('admin.users.create', array()) }}" class="btn btn-success">New User</a>
                                <a href="{{ URL::route('admin.users.createQuickPatient', array('blogId' => '7')) }}" class="btn btn-success">Participant Quick Add (Program 7)</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <div class="panel-body">

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.users.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        </div>

                        <h2>Filter</h2>
                        <div class="row" style="margin:20px 0px 40px 0px;">
                            <div class="col-md-8 col-md-offset-2">
                                <div class="row">
                                    <div class="col-xs-4 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                    <div class="col-xs-8">{!! Form::select('filterUser', array('all' => 'All Users') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2 text-right">{!! Form::label('filterRole', 'Role:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('filterRole', array('all' => 'All Roles') + $roles, $filterRole, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                <div class="col-xs-2 text-right">{!! Form::label('filterProgram', 'Program:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('filterProgram', array('all' => 'All Programs') + $programs, $filterProgram, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="" style="text-align:center;">
                                    {!! Form::hidden('action', 'filter') !!}
                                    {!! Form::submit('Apply Filters', array('class' => 'btn btn-orange')) !!}
                                    {!! Form::submit('Reset Filters', array('class' => 'btn btn-orange')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>ID</strong></td>
                                <td><strong>Role</strong></td>
                                <td><strong>user_login</strong></td>
                                <td><strong>user_email</strong></td>
                                <td><strong>status</strong></td>
                                <td><strong>display_name</strong></td>
                                <td><strong>blog</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpUsers) > 0)
                                @foreach( $wpUsers as $wpUser )
                                    <tr>
                                        <td>
                                            @if(Entrust::can('users-edit-all'))
                                                <a href="{{ URL::route('admin.users.edit', array('id' => $wpUser->ID)) }}" class="btn btn-primary btn-xs">{{ $wpUser->ID }}</a><br />
                                            @endif
                                        </td>
                                        <td>
                                            @if (count($wpUser->roles) > 0)
                                                @if($wpUser->hasRole('participant'))
                                                    <div style="margin-left:10px;">
                                                        <a href="{{ URL::route('patient.summary', array('patientId' => $wpUser->ID)) }}" class="btn btn-orange btn-xs">Participant</a>
                                                    </div>
                                                @else
                                                    @foreach ($wpUser->roles as $role)
                                                        @if(Entrust::can('users-edit-all'))
                                                            <a href="{{ URL::route('admin.users.edit', array('id' => $wpUser->ID)) }}" class="btn btn-info btn-xs">{{ $role->display_name }}</a><br />
                                                        @endif
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->user_login }}</td>
                                        <td>{{ $wpUser->user_email }}</td>
                                        <td>{{ $wpUser->user_status }}</td>
                                        <td>{{ $wpUser->display_name }}</td>
                                        <td>{{ $wpUser->program_id }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No users found</td></tr>
                            @endif
                            </tbody>
                        </table>

                        @if (count($wpUsers) > 0)
                            {!! $wpUsers->appends(['action' => 'filter', 'filterUser' => $filterUser, 'filterRole' => $filterRole, 'filterProgram' => $filterProgram])->render() !!}
                        @endif

                        @if (count($invalidUsers) > 0)
                            <h2>Invalid Users</h2>
                            <h3>Missing Config</h3>
                            @foreach( $invalidUsers as $user )
                                User {{ $user->ID }} - {{ $user->display_name }}<br>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
