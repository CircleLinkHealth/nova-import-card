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
        <h3 align="center">Create Call for existing Note</h3>
        <hr><br>
        <div class="text-center">
            <div>
                <h4 class="ops-dboard-title">Note Info</h4>
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <td>Note ID:</td>
                        <td>{{$note->id}}</td>
                    </tr>
                    <tr>
                        <td>Note type:</td>
                        <td>{{$note->type}}</td>
                    </tr>
                    <td>Date Performed:</td>
                    <td>{{$note->performed_at}}</td>
                </table>
            </div>
        </div>
        <div class="text-center">
            <form action="{{route('CallsDashboard.create-call')}}" method="POST">
                <br>
                <div class="form-group">
                    @isset($message)
                        <div class="row col-lg-12">
                            <div class="alert alert-info">
                                <span>{{ $message }}</span>
                            </div>
                        </div>
                    @endisset
                </div>
                <div class="form-group">
                    <div class="form-group">
                        <h3>Create Call:</h3>
                        <br>
                        <div class="form-group">
                            <h5>Assign Nurse to Call:</h5>
                            <select name="nurseId" class="select2">
                                <option value="none">Nurses</option>
                                @foreach($nurses as $n)
                                    <option value="{{$n->id}}">{{$n->display_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <h5>Select Call status:</h5>
                            <input type="radio" name="status" value="reached" required> Successful<br>
                            <input type="radio" name="status" value="not reached"> Unsuccessful<br>
                        </div>
                        <div class="form-group">
                            <h5>Select Call direction: (defaults to Outbound)</h5>
                            <input type="radio" name="direction" value="inbound" required> Inbound<br>
                            <input type="radio" name="direction" value="outbound" checked> Outbound<br>
                        </div>
                    </div>
                </div>
                <br>
                <input type="hidden" name="noteId" value="{{$note->id}}">
                <input align="center" type="submit" value="Submit" class="btn btn-info">
                {{csrf_field()}}
            </form>
        </div>
    </div>
@endsection