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
                                            <a href='/locations/show/{{ $id }}'>{{ $id }} -- {{ $loc }}</a> <BR>
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
