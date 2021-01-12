@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush
    <div class="container">
        <h3 align="center">Edit/Delete Observations for a User</h3>
    </div>
    <div class="container">
        <div class="col-md-6">
            <div class="input-group">
                <div>
                    <form action="{{route('observations-dashboard.list')}}" method="GET">
                        <div class="form-group">
                            <p>Insert User Id:</p>
                            <input type="number" name="userId"required>
                        </div>
                        <div>
                            <input align="center" type="submit" value="Submit" class="btn btn-info">
                        </div>
                        @if (session('msg'))
                            <div class="alert alert-success">
                                {{ session('msg') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
    @isset($user)
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">
                Observations List for: {{$user->display_name}}.
            </div>
            <div class="panel-body">
                <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search by key.." title="Type in a name">
                <table id="myTable" class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <th>Observation Key</th>
                        <th>Value</th>
                        <th>Method</th>
                        <th>Message Id</th>
                        <th>Practice Id</th>
                        <th>Date</th>
                        <th> </th>
                        <th> </th>
                    </tr>
                    @foreach($observations as $o)
                        <tr>
                            <td>{{$o->obs_key}}</td>
                            <td>{{$o->obs_value}}</td>
                            <td>{{$o->obs_method}}</td>
                            <td>{{$o->obs_message_id}}</td>
                            <td>{{$o->program_id}}</td>
                            <td>{{$o->obs_date}}</td>
                            <td><form action="{{route('observations-dashboard.edit')}}" method="GET">
                                                <input type="hidden" name="obsId" value="{{$o->id}}">
                                                <input align="center" type="submit" value="Edit" class="btn btn-warning">
                                                <br>
                                            </form>
                                        </td>
                            <td>
                                <form action="{{route('observations-dashboard.delete')}}" onsubmit="return confirmObservationDelete()" method="POST">
                                    {{ csrf_field() }}
                                    {!! method_field('delete') !!}
                                    <input type="hidden" name="obsId" value="{{$o->id}}">
                                    <input align="center" type="submit" value="Delete" class="btn btn-danger">
                                    <br>
                                </form>
                            </td>
                        </tr>

                    @endforeach

                </table>
                @push('scripts')
                    <script>
                        function confirmObservationDelete(e) {
                            return confirm('Are you sure you want to delete this observation?')
                        }

                        function checked() {
                            let input, filter, table, tr, td, i;
                            input = document.getElementById("myInput");
                            filter = input.value.toUpperCase();
                            table = document.getElementById("myTable");
                            tr = table.getElementsByTagName("tr");
                            for (i = 0; i < tr.length; i++) {
                                td = tr[i].getElementsByTagName("td")[0];
                                if (td) {
                                    if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                                        tr[i].style.display = "";
                                    } else {
                                        tr[i].style.display = "none";
                                    }
                                }
                            }
                        }
                    </script>
                @endpush
                {!! $observations->appends(Request::except('page'))->links() !!}
            </div>
        </div>
    </div>
    @endisset
@endsection