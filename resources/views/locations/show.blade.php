@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Locations</div>
                    <div class="panel-body">
                        @include('errors.errors')
<strong>Parent Locations:</strong><BR>
                                        @foreach( $locationParents as $id => $loc )
<<<<<<< HEAD
                                            <a href='/locations/{{ $id }}'>{{ $id }} -- {{ $loc }}</a> <BR>
=======
                                            <a href="{{ action('LocationController@show', $id) }}">{{ $id }} -- {{ $loc }}</a> <BR>
>>>>>>> e9f5858661d87b17c00404877a6782c66a23c11a
                                        @endforeach

<strong>Parents Sub Locations:</strong><BR>
                                        @foreach( $locationParentsSubs as $id => $loc )
                                            {{ $id }} -- {{ $loc }} <BR>
                                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
