@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Users</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ url('/wpusers/create') }}" class="btn btn-success">New User</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <div class="panel-body">
                        <p>These users are coming from the wp_users.. this laravel project has been modified to use wp_users as the primary users table.</p>
                        <p><strong>See bottom of page for list of invalid users</strong></p>

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('wpusers.index', array()), 'class' => 'form-horizontal')) !!}
                        </div>

                        <h3>Filter</h3>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">{!! Form::label('filterRole', 'Role:') !!}</div>
                                <div class="col-xs-4">{!! Form::select('filterRole', array('all' => 'All Roles') + $roles, $filterRole, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                <div class="col-xs-2">{!! Form::label('filterProgram', 'Program:') !!}</div>
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
                                <td><strong>user_nicename</strong></td>
                                <td><strong>user_email</strong></td>
                                <td><strong>status</strong></td>
                                <td><strong>display_name</strong></td>
                                <td><strong>roles</strong></td>
                                <td><strong>blog</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpUsers) > 0)
                                @foreach( $wpUsers as $wpUser )
                                    <tr>
                                        <td>
                                            @if($wpUser->blogId())
                                                <a href="{{ url('wpusers/'.$wpUser->ID.'/edit') }}" class="btn btn-primary">{{ $wpUser->ID }} Edit</a>
                                            @else
                                                <a href="{{ url('wpusers/'.$wpUser->ID.'/edit') }}" class="btn btn-danger">{{ $wpUser->ID }} Edit</a>
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->user_nicename }}</td>
                                        <td>{{ $wpUser->user_email }}</td>
                                        <td>{{ $wpUser->user_status }}</td>
                                        <td>{{ $wpUser->display_name }}</td>
                                        <td>
                                            @if (count($wpUser->roles) > 0)
                                                <ul>
                                                @foreach ($wpUser->roles as $role)
                                                    <li>{{ $role->display_name }}</li>
                                                @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->blogId() }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No users found</td></tr>
                            @endif
                            </tbody>
                        </table>

                        @if (count($wpUsers) > 0)
                            {!! $wpUsers->appends(['action' => 'filter', 'filterRole' => $filterRole, 'filterProgram' => $filterProgram])->render() !!}
                        @endif

                        @if (count($invalidWpUsers) > 0)
                            <h2>Invalid Users</h2>
                            <h3>Missing Config</h3>
                            @foreach( $invalidWpUsers as $wpUser )
                                User {{ $wpUser->ID }} - {{ $wpUser->display_name }}<br>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
