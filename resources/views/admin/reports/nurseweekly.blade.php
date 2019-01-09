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
                                <table class="table table-striped" id="nurse_daily">
                                    <thead>


                                            <tr>
                                                <th>Name</th>
                                                <th>Scheduled calls</th>
                                            </tr>
                                    </thead>
                                    @foreach ($nurses as $nurse)
                                    <tbody>

                                        <tr>
                                            <td>{{$nurse->getFullName()}}</td>
                                            <td>{{$nurse->outboundCalls->count()}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                                  <br>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection