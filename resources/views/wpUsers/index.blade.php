@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <p>These users are coming from the wp_users.. this laravel project has been modified to use wp_users as the primary users table.</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>ID</strong></td>
                            <td><strong>user_login</strong></td>
                            <td><strong>user_nicename</strong></td>
                            <td><strong>user_email</strong></td>
                            <td><strong>status</strong></td>
                            <td><strong>display_name</strong></td>
                            <td><strong>blog</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $wpUsers as $wpUser )
                            <tr>
                                <td><a href="{{ url('wpusers/'.$wpUser->ID.'/edit') }}" class="btn btn-primary">{{ $wpUser->ID }} Edit</a></td>
                                <td>{{ $wpUser->user_login }}</td>
                                <td>{{ $wpUser->user_nicename }}</td>
                                <td>{{ $wpUser->user_email }}</td>
                                <td>{{ $wpUser->user_status }}</td>
                                <td>{{ $wpUser->display_name }}</td>
                                <td>{{ $wpUser->blogId() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
