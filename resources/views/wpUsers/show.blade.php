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
                    <div class="panel-heading">User ID: {{ $wpUser->id }}</div>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>email</strong></td>
                                <td><strong>first_name</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $wpUser->user_email }}</td>
                                <td>need this for meta <a href="https://github.com/chrismichaels84/eloquent-meta">https://github.com/chrismichaels84/eloquent-meta</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <h1>Activities</h1>
                        <p>Current monthly total (recalculated on the fly) : {{ ($activityTotal / 60) }}</p>
                        <a href="/wpusers/{{ $wpUser->ID }}?action=recalcActivities" class="recalcActivities"><span class="glyphicon glyphicon-refresh"></span> Recalculate Monthly Total</a>

                    <br />
                    <br />
                    <br />
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop