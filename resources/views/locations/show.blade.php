@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Location Detail</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ action('LocationController@index') }}" class="btn btn-danger">Back</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Locations</div>
                    <div class="panel-body">
                        @include('errors.errors')
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
                                            <a href='/admin/locations/{{ $id }}'>{{ $id }} -- {{ $loc }}</a>
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


                        <h2>Parents Sub Locations:</h2>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Detail</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($locationParentsSubs) > 0)
                                @foreach( $locationParentsSubs as $id => $loc )
                                    <tr>
                                        <td>
                                            <a href='/admin/locations/{{ $id }}'>{{ $id }} -- {{ $loc }}</a>
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('locations.edit', array('id' => $id)) }}" class="btn btn-info btn-xs">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No sub locations found</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
