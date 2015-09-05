@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="row">
                        <div class="col-sm-8">
                            <h1>Users</h1>
                        </div>
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ url('admin/users/create') }}" class="btn btn-success">New User</a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-heading">Users</div>
                    <br>
                    <p>These users are coming from the wp_users.. this laravel project has been modified to use wp_users as the primary users table.</p>
                    <p><strong>See bottom of page for list of invalid users</strong></p>
                    <br>
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
                        @foreach( $wpUsers as $wpUser )
                            <tr>
                                <td>
                                    @if($wpUser->blogId())
                                        <a href="{{ url('wpusers/'.$wpUser->ID.'/edit') }}" class="btn btn-primary">{{ $wpUser->ID }} Edit</a>
                                    @else
                                        {{ $wpUser->ID }}
                                    @endif
                                </td>
                                <td>{{ $wpUser->user_nicename }}</td>
                                <td>{{ $wpUser->user_email }}</td>
                                <td>{{ $wpUser->user_status }}</td>
                                <td>{{ $wpUser->display_name }}</td>
                                <td>
                                    @if (count($wpUser->roles()) > 0)
                                        @foreach ($wpUser->roles() as $role)
                                            <li>{{ $role->name }}</li>
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{ $wpUser->blogId() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <h2>Invalid Users</h2>
                    <h3>Missing Config</h3>
                    @if (isset($invalidWpUsers))
                        @foreach( $invalidWpUsers as $wpUser )
                            User {{ $wpUser->ID }} - {{ $wpUser->display_name }}<br>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
