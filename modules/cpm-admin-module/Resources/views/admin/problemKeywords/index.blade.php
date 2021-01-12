@extends('cpm-admin::partials.adminUI')


@section('content')
    @push('styles')
        <style>
            .ops-dboard-title {
                background-color: #eee;
                padding: 2rem;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <h3 align="center">Manage CPM Problems</h3>

        <table id="myTable" class="table table-striped table-bordered table-curved table-condensed table-hover">
            <tr>
                <th>Problem Name <br> <input type="text" id="nameInput" onkeyup="filterByName()"
                                             placeholder="Search.."></th>
                <th>Keywords <br> <input type="text" id="keywordInput" onkeyup="filterByKeywords()"
                                         placeholder="Search.."></th>
                <th>Default ICD10 Code</th>
                <th>Is Behavioural</th>
                <th>Weight</th>
                <th></th>
            </tr>
            @foreach($problems as $p)
                <tr>
                    <td>{{$p->name}}</td>
                    <td>{{$p->contains}}</td>
                    <td>{{$p->default_icd_10_code}}</td>
                    <td>{{$p->is_behavioral}}</td>
                    <td>{{$p->weight}}</td>
                    <td>
                        <form action="{{route('manage-cpm-problems.edit')}}" method="GET">
                            <input type="hidden" name="problem_id" value="{{$p->id}}">
                            <input align="center" type="submit" value="Edit" class="btn btn-warning">
                            <br>
                        </form>
                    </td>
                </tr>

            @endforeach

        </table>

        @push('scripts')
            <script>
                function filterByName() {
                    let input, filter, table, tr, td, i;
                    input = document.getElementById("nameInput");
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

                function filterByKeywords() {
                    let input, filter, table, tr, td, i;
                    input = document.getElementById("keywordInput");
                    filter = input.value.toUpperCase();
                    table = document.getElementById("myTable");
                    tr = table.getElementsByTagName("tr");
                    for (i = 0; i < tr.length; i++) {
                        td = tr[i].getElementsByTagName("td")[1];
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
    </div>

@endsection