@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ $rule->rule_name }}</div>

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
                            <td><strong>active</strong></td>
                            <td><strong>actions</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $rule->id }}</td>
                                <td>{{ $rule->rule_name }}</td>
                                <td>{{ $rule->rule_description }}</td>
                                <td>{{ $rule->active }}</td>
                                <td>{{ $rule->type_id }}</td>
                                <td>{{ $rule->effective_date }}</td>
                                <td>{{ $rule->expiration_date }}</td>
                                <td>{{ $rule->active }}</td>
                                <td><a href="{{ URL::route('admin.rules.edit', array('id' => $rule->id)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                        </tbody>
                    </table>
                    <strong>Summary:</strong>
                    <p>{{ $rule->summary }}</p>

                    <strong>Conditions:</strong>
                    @foreach( $rule->intrConditions as $intrCondition )
                        <ul>
                            <li>{{ $intrCondition->id }}</li>
                            <li>{{ $intrCondition->condition->condition_name }} [{{ $intrCondition->condition->condition }}]</li>
                            <li>{{ $intrCondition->value }}</li>
                        </ul>
                    @endforeach

                    <strong>Actions:</strong>
                    @foreach( $rule->intrActions as $intrAction )
                        <ul>
                            <li>{{ $intrAction->id }}</li>
                            <li>{{ $intrAction->action->action_name }} [{{ $intrAction->action->action }}]</li>
                            <li>{{ $intrAction->value }}</li>
                        </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop
