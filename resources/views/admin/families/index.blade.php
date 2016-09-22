@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <script type="text/javascript" src="{{ asset('/js/admin/families.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Families</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.families.create', array()) }}" class="btn btn-success" disabled="disabled">Create Family</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Families</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.families.index', array()), 'class' => 'form-horizontal')) !!}
                        </div>

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>id</strong></td>
                                <td><strong>name</strong></td>
                                <td><strong>view</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $families as $family )
                                <tr>
                                    <td><a href="{{ URL::route('admin.families.show', array('id' => $family->id)) }}" class="btn btn-primary">{{ $family->id }}</a></td>
                                    <td><a href="{{ URL::route('admin.families.show', array('id' => $family->id)) }}" class="btn btn-primary">{{ $family->id }}</a></td>
                                    <td>
                                        @if(Entrust::can('users-edit'))
                                            <a href="{{ URL::route('admin.families.edit', array('id' => $family->id)) }}" class="btn btn-primary">Edit</a>
                                        @endif
                                        @if(Entrust::can('users-destroy'))
                                            <a href="{{ URL::route('admin.families.destroy', array('id' => $family->id)) }}" class="btn btn-warning">Remove</a>
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
    </div>
@stop
