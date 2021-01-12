@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Locations</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ route('locations.create') }}" class="btn btn-success">New Location</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('core::partials.errors.errors')

                        <h3>Locations:</h3>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Detail</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($locations) > 0)
                                @foreach( $locations as $loc )
                                    <tr>
                                        <td>
                                            <a href="{{ route('locations.edit', array('id' => $loc->id)) }}">{{ $loc->name }}</a>
                                        </td>
                                        <td>
                                            <a href="{{ route('locations.edit', array('id' => $loc->id)) }}"
                                               class="btn btn-info btn-xs">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No parent locations found</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
