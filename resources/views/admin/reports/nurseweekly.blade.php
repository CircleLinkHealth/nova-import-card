@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>


        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Nurse Weekly Report</div>
                            <div class="panel-body">

                                @foreach ($nurses as $nurse)
                                    {{$nurse['name']}}
                                    {{$nurse['scheduledCalls']}}
                                    {{$nurse['actualCalls']}}
                                    {{$nurse['successful']}}
                                    {{$nurse['unsuccessful']}}<br>
                                @endforeach

                            </div>

                            <br>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection