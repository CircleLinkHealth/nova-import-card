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
                    <input type="submit">
                </form>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <div>
                <form action="{{route('problem-keywords.update')}}" method="POST">
                    {!! method_field('patch') !!}
                    Add keyword to text below. (Remember to seperate by comma!)
                    @if($problem != null)
                    <textarea class="col-md-12" name="contains">{{$problem->contains}}</textarea>
                    <input type="hidden" name="problemId" value="{{$problem->id}}">
                        @else <textarea class="col-md-12" name="contains">Select Problem</textarea>
                    <input type="hidden" name="problemId" value="{{null}}">
                    @endif
                    <input type="submit">
                    {{csrf_field()}}
                </form>
                <br>
            </div>
        </div>
        <br>
    </div>
@endsection
