@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Locations</div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
