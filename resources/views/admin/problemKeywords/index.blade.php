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
        <h3 align="center">Manage CPM Problems</h3>
    </div>
    {{--<div class="container">--}}
        {{--<div class="col-md-6">--}}
            {{--<div class="input-group">--}}
                {{--<div>--}}
                    {{--<form action="{{route('observations-dashboard.list')}}" method="GET">--}}
                        {{--<div class="form-group">--}}
                            {{--<p>Search by Name:</p>--}}
                            {{--<input type="text" name="keyword" required>--}}
                        {{--</div>--}}
                        {{--<div>--}}
                            {{--<input align="center" type="submit" value="Submit" class="btn btn-info">--}}
                        {{--</div>--}}
                        {{--@if (session('msg'))--}}
                            {{--<div class="alert alert-success">--}}
                                {{--{{ session('msg') }}--}}
                            {{--</div>--}}
                        {{--@endif--}}
                    {{--</form>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Problems List
                </div>
                <div class="panel-body">
                    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Filter by name.." title="Type in a name">
                    <table id="myTable" class="table table-striped table-bordered table-curved table-condensed table-hover">
                        <tr>
                            <th>Problem Name</th>
                            <th>Keywords</th>
                            <th>Default ICD10 Code</th>
                            <th>Is Behavioural</th>
                            <th>Weight</th>
                            <th> </th>
                        </tr>
                        @foreach($problems as $p)
                            <tr>
                                <td>{{$p->name}}</td>
                                <td>{{$p->contains}}</td>
                                <td>{{$p->default_icd_10_code}}</td>
                                <td>{{$p->is_behavioral}}</td>
                                <td>{{$p->weight}}</td>
                                <td><form action="{{route('manage-cpm-problems.edit')}}" method="GET">
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
                            function confirmObservationDelete(e) {
                                return confirm('Are you sure you want to delete this observation?')
                            }

                            function myFunction() {
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
                    {{--{!! $problems->appends(Input::except('page'))->links() !!}--}}
                </div>
            </div>
        </div>
@endsection
{{--@section('content')--}}
    {{--@push('styles')--}}
        {{--<style>--}}
            {{--.ops-dboard-title {--}}
                {{--background-color: #eee;--}}
                {{--padding: 2rem;--}}
            {{--}--}}
        {{--</style>--}}
    {{--@endpush--}}
    {{--<div class="container">--}}
        {{--<h3 align="center">Assign Keywords to CPM Problems</h3>--}}
        {{--<br>--}}
        {{--<hr>--}}
        {{--<br>--}}
        {{--<div class="text-center">--}}
            {{--<div>--}}
                {{--<form action="{{route('problem-keywords.edit')}}">--}}
                    {{--<select name="problem_id" class="select2">--}}
                        {{--<option value="none">CPM Problems</option>--}}
                        {{--@foreach($problems as $p)--}}
                            {{--<option value="{{$p->id}}">{{$p->name}}</option>--}}
                        {{--@endforeach--}}
                    {{--</select>--}}
                    {{--<input type="submit" class="btn btn-info">--}}
                {{--</form>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<hr>--}}
        {{--<div class="text-center">--}}
            {{--<div>--}}
                {{--<form action="{{route('problem-keywords.update')}}" method="POST">--}}
                    {{--{!! method_field('patch') !!}--}}
                    {{--<div class="form-group">--}}
                        {{--Edit keywords for CPM Problem @if($problem != null): <strong>{{$problem->name}}</strong>. @elseif($problem == null) -select problem above. @endif (Remember to seperate by comma!)--}}
                    {{--</div>--}}
                    {{--@if($problem != null)--}}
                    {{--<textarea class="col-md-12 form-group" name="contains">{{$problem->contains}}</textarea>--}}
                        {{--<textarea class="col-md-12 form-group" name="default_icd_10_code">{{$problem->default_icd_10_code}}</textarea>--}}
                        {{--<input type="radio" name="gender" value="1"> True<br>--}}
                        {{--<input type="radio" name="gender" value="0"> False<br>--}}
                        {{--<textarea class="col-md-12 form-group" name="is_behavioural">{{$problem->is_behavioral}}</textarea>--}}
                    {{--<input type="hidden" name="problemId" value="{{$problem->id}}">--}}
                    {{--@else--}}
                        {{--<textarea class="col-md-12" name="contains">Select CPM Problem.</textarea>--}}
                    {{--<input type="hidden" name="problemId" value="{{null}}">--}}
                    {{--@endif--}}
                    {{--<div class="form-group">--}}
                        {{--<input type="submit" class="btn btn-info">--}}
                    {{--</div>--}}
                    {{--{{csrf_field()}}--}}
                {{--</form>--}}
                {{--<br>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<div class="alert-success col-md-4 col-md-offset-4">--}}
            {{--@isset($message){{$message}}@endisset--}}
        {{--</div>--}}
        {{--<br>--}}
    {{--</div>--}}
{{--@endsection--}}
