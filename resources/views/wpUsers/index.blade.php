@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <div class="container-fluid">
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
                                {{-- <a href="{{ URL::route('admin.users.createQuickPatient', array('blogId' => '7')) }}" class="btn btn-success">Participant Quick Add (Program 7)</a> --}}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <div class="panel-body">
                        @include('errors.errors')

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
                                    <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-sort"></i> Apply Filters</button>
                                    <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-refresh"></i> Reset Filters</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Role</strong></td>
                                <td><strong>Email</strong></td>
                                <td><strong>Program</strong></td>
                                <td><strong>Actions</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpUsers) > 0)
                                @foreach( $wpUsers as $wpUser )
                                    <tr>
                                        <td><a href="{{ URL::route('admin.users.edit', array('id' => $wpUser->ID)) }}" class=""> {{ $wpUser->fullNameWithID }}</a></td>
                                        <td>
                                            @if (count($wpUser->roles) > 0)
                                                @if($wpUser->hasRole('participant'))
                                                    @foreach($wpUser->roles as $role)
                                                        {{ $role->display_name }}
                                                    @endforeach
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->user_email }}</td>
                                        <td>
                                            @if ($wpUser->primaryProgram)
                                                <a href="{{ URL::route('admin.programs.show', array('id' => $wpUser->primaryProgram->blog_id)) }}" class=""> {{ $wpUser->primaryProgram->display_name }}</a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if(Entrust::can('users-edit-all'))
                                                <a href="{{ URL::route('admin.users.edit', array('id' => $wpUser->ID)) }}" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @endif
                                            @if (count($wpUser->roles) > 0)
                                                @if($wpUser->hasRole('participant'))
                                                    <a href="{{ URL::route('patient.summary', array('patientId' => $wpUser->ID)) }}" class="btn btn-info btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-eye-open"></i> Provider UI</a>
                                                @endif
                                            @endif
                                            @if(Entrust::can('users-edit-all'))
                                                <a href="{{ URL::route('admin.users.destroy', array('id' => $wpUser->ID)) }}" class="btn btn-danger btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-remove-sign"></i> Delete</a>
                                            @endif
                                        </td>
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
