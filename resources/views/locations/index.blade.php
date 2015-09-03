@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">View all locations</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        @foreach( $locations as $loc )

                            <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('LocationController@destroy', $loc->id) }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input name="_method" type="hidden" value="DELETE">

                                <div class="form-group">
                                    <div class="col-md-6">
                                        <h4>{{ $loc->name }}</h4>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">
                                            Delete Location
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endforeach


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
