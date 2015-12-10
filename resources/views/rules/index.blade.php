@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
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
                            <td><strong>actions</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $rules as $rule )
                            <tr>
                                <td><a href="{{ URL::route('admin.rules.show', array('id' => $rule->id)) }}" class="btn btn-primary">{{ $rule->id }} Detail</a></td>
                                <td>{{ $rule->rule_name }}</td>
                                <td>{{ $rule->rule_description }}</td>
                                <td>{{ $rule->active }}</td>
                                <td>{{ $rule->type_id }}</td>
                                <td>{{ $rule->effective_date }}</td>
                                <td>{{ $rule->expiration_date }}</td>
                                <td><a href="{{ URL::route('admin.rules.edit', array('id' => $rule->id)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $rules->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
