@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
                @include('errors.messages')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>App Configs</h1>
                    </div>
                    @if(Cerberus::can('app-config-manage'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('admin.appConfig.create', array()) }}" class="btn btn-success">New App Config</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All App Configs</div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td></td>
                            <td><strong>id</strong></td>
                            <td><strong>name</strong></td>
                            <td><strong>value</strong></td>
                            <td><strong>created at</strong></td>
                            <td><strong>updated at</strong></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $appConfigs as $appConfig )
                            <tr>
                                <td><a href="{{ URL::route('admin.appConfig.show', array('id' => $appConfig->id)) }}" class="btn btn-primary">Detail</a></td>
                                <td>{{ $appConfig->id }}</td>
                                <td>{{ $appConfig->config_key }}</td>
                                <td>{{ $appConfig->config_value }}</td>
                                <td>{{ date('F d, Y g:i A', strtotime($appConfig->created_at)) }}</td>
                                <td>{{ date('F d, Y g:i A', strtotime($appConfig->updated_at)) }}</td>
                                <td>
                                    @if(Cerberus::can('app-config-manage'))
                                        <a href="{{ URL::route('admin.appConfig.edit', array('id' => $appConfig->id)) }}" class="btn btn-primary">Edit</a>
                                        <a href="{{ URL::route('admin.appConfig.destroy', array('id' => $appConfig->id)) }}" class="btn btn-danger">Delete</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $appConfigs->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
