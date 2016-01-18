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
                            <a href="{{ action('LocationController@create') }}" class="btn btn-success">New Location</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('errors.errors')

                        <h3>Parent Locations:</h3>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Detail</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($locationParents) > 0)
                                @foreach( $locationParents as $id => $loc )
                                    <tr>
                                        <td>
                                            <a href='/admin/locations/{{ $id }}'>{{ $loc }} ({{ $id }})</a>
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('locations.edit', array('id' => $id)) }}" class="btn btn-info btn-xs">Edit</a>
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
