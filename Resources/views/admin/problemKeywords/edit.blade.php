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
    <div class="container">
        <div align="center"><span>Showing Details for </span>
            <h3 style="display: inline-block;"><strong>{{$problem->name}}</strong></h3></div>

        @if (session('msg'))
            <div class="alert alert-success">
                {{ session('msg') }}
            </div>
        @endif

        <form action="{{route('manage-cpm-problems.update')}}" onsubmit="return confirmObservationSubmit()"
              method="POST">
            {!! method_field('patch') !!}

            {{csrf_field()}}


            <table class="table table-striped table-bordered table-curved table-condensed table-hover">

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
                        <input type="radio" name="is_behavioral"
                               value="1" {{($problem->is_behavioral == 1)?'checked':''}}> True
                        <br>
                        <input type="radio" name="is_behavioral"
                               value="0" {{($problem->is_behavioral == 0)?'checked':''}}> False
                    </td>
                </tr>
                <tr>
                    <th>Weight. How much preference should the system give this condition for billing? Higher number
                        means higher preference. <br><br>Example:<br> Patient has Diabetes (Weight: 100), Hypertension
                        (Weight: 80) and Smoking (Weight: 1). When choosing problems for billing the system will prefer
                        to choose Diabetes and Hypertension over Smoking.
                    </th>
                    <td>
                        <input type="number" min="1" name="weight" value="{{$problem->weight}}">
                    </td>
                </tr>


            </table>

            <div>

                <div class="form-group text-right">
                    <input type="hidden" name="problem_id" value="{{$problem->id}}">
                    <input type="submit" value="Save Changes" class="btn btn-success">

                    <a class="btn btn-default" href="{{route('manage-cpm-problems.index')}}">Go Back to Index</a>
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
@endsection