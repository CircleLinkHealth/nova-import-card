@extends('partials.adminUI')

@section('content')
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
                        <h1>Nurse Time Report</h1>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading"></div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            @foreach( $reportColumns as $column )
                                <td>{{ $column }}</td>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $reportRows as $reportRow )
                            <tr>
                                @foreach( $reportRow as $column )
                                    <td>{{ $column }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
