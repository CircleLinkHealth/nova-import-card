@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
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
                    <div class="panel-heading">All Rules</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>rule_name</strong></td>
                            <td><strong>rule_description</strong></td>
                            <td><strong>active</strong></td>
                            <td><strong>type_id</strong></td>
                            <td><strong>effective_date</strong></td>
                            <td><strong>expiration_date</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="{{ url('rules/'.$rule->id.'/') }}">{{ $rule->id }}</a></td>
                                <td>{{ $rule->rule_name }}</td>
                                <td>{{ $rule->rule_description }}</td>
                                <td>{{ $rule->active }}</td>
                                <td>{{ $rule->type_id }}</td>
                                <td>{{ $rule->effective_date }}</td>
                                <td>{{ $rule->expiration_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <strong>Summary:</strong>
                    <p>{{ $rule->summary }}</p>
                </div>
            </div>
        </div>
    </div>
@stop
