@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @if (isset($error))
                    <div class="alert alert-danger">
                        <ul>
                            <li>{{ $error }}</li>
                        </ul>
                    </div>
                @endif

                @if (isset($success))
                    <div class="alert alert-success">
                        <ul>
                            <li>{{ $success }}</li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <p>These users are coming from the wp_users.. this laravel project has been modified to use wp_users as the primary users table.</p>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>ID</strong></td>
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
                                    @if($wpUser->blogId())
                                        <strong>{{ $wpUser->blogId() }}</strong>
                                    @elseif(is_numeric(substr($wpUser->user_email, 0, 1)))
                                        {{ $wpUser->nickname  }}<a href="/wpusers/{{ $wpUser->ID }}?action=setPatientToBlog&blogId={{ substr($wpUser->user_email, 0, 1) }}" class="setPatientToBlog">Add primary_blog={{ substr($wpUser->user_email, 0, 1) }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
