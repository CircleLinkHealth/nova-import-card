@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Role</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Role: {{ $role->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>name</strong></td>
                                <td><strong>display name</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->display_name }}</td>
                                <td>
                                    @if(Entrust::can('roles-manage'))
                                        <a href="{{ URL::route('roles.edit', array('id' => $role->id)) }}"
                                           class="btn btn-primary">Edit</a>
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <h3>Permissions:</h3>
                            <div id="permissions">
                                @foreach( $role->perms as $permission )
                                    <div class="col-sm-12">
                                        <strong>{!! $permission->display_name !!}</strong> [{!! $permission->name !!}]
                                    </div>
                                @endforeach
                                <br/><br/>
                            </div>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Role Name:</strong><br>
                            {{ $role->name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Display Name:</strong><br>
                            {{ $role->display_name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            <p>{{ $role->description }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Created:</strong><br>
                            {{ date('F d, Y g:i A', strtotime($role->created_at)) }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
