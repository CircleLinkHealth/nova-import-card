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
        <h3 align="center">Assign Keywords to CPM Problems</h3>
        <br>
        <hr>
        <br>
        <div class="text-center">
            <div>
                <form action="{{route('problem-keywords.edit')}}">
                    <select name="problem_id" class="select2">
                        <option value="none">CPM Problems</option>
                        @foreach($problems as $p)
                            <option value="{{$p->id}}">{{$p->name}}</option>
                        @endforeach
                    </select>
                    <input type="submit" class="btn btn-info">
                </form>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <div>
                <form action="{{route('problem-keywords.update')}}" method="POST">
                    {!! method_field('patch') !!}
                    <div class="form-group">
                        Edit keywords for CPM Problem @if($problem != null): <strong>{{$problem->name}}</strong>. @elseif($problem == null) -select problem above. @endif (Remember to seperate by comma!)
                    </div>
                    @if($problem != null)
                    <textarea class="col-md-12 form-group" name="contains">{{$problem->contains}}</textarea>
                    <input type="hidden" name="problemId" value="{{$problem->id}}">
                    @else
                        <textarea class="col-md-12" name="contains">Select CPM Problem.</textarea>
                    <input type="hidden" name="problemId" value="{{null}}">
                    @endif
                    <div class="form-group">
                        <input type="submit" class="btn btn-info">
                    </div>
                    {{csrf_field()}}
                </form>
                <br>
            </div>
        </div>
        <div class="alert-success col-md-4 col-md-offset-4">
            @isset($message){{$message}}@endisset
        </div>
        <br>
    </div>
@endsection
