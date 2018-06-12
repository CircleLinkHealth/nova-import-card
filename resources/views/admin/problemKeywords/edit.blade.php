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
        <h3 align="center">Showing Details for <strong>{{$problem->name}}</strong></h3>
        <br>
    </div>
    <div class="container">
        <div>
            <form action="{{route('manage-cpm-problems.index')}}" method="GET">
                <input align="left" type="submit" value="Return to list" class="btn btn-info">
                <br>
            </form>
            <form action="{{route('manage-cpm-problems.update')}}" onsubmit="return confirmObservationSubmit()" method="POST">
                {!! method_field('patch') !!}
                {{csrf_field()}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                    </div>
                    <br>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                            <p><strong>Editable Data:</strong> (warning: the data in text-boxes will be stored in the database exactly as edited here)</p>
                            <tr>
                                <th>Keywords (Remember to separate by comma!)</th>
                                <td>
                                    <textarea name="contains">{{$problem->contains}}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <th>Default ICD10 Code</th>
                                <td>
                                    <textarea name="default_icd_10_code">{{$problem->default_icd_10_code}}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <th>Is Behavioral</th>
                                <td>
                                    <input type="radio" name="is_behavioral" value="1" {{($problem->is_behavioral == 1)?'checked':''}}> True
                                    <br>
                                    <input type="radio" name="is_behavioral" value="0" {{($problem->is_behavioral == 0)?'checked':''}}> False
                                </td>
                            </tr>
                            <tr>
                                <th>Weight</th>
                                <td>
                                    <input type="number" min="1" max="10" name="weight" value="{{$problem->weight}}">
                                </td>
                            </tr>


                        </table>
                    </div>
                    <div>

                        <div class="form-group">
                            <input type="hidden" name="problem_id" value="{{$problem->id}}">
                            <input type="submit" value="Submit Changes" class="btn btn-info">
                            @if (session('msg'))
                                <div class="alert alert-success">
                                    {{ session('msg') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            @push('scripts')
                <script>
                    function confirmObservationSubmit() {
                        return confirm('Are you sure you want to change these details about {{$problem->name}}?')
                    }
                </script>
            @endpush
        </div>
    </div>
@endsection