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
        <h3 align="center">Edit Call Status</h3>
        <hr>
        <br>
        <form action="{{route('CallsDashboard.index')}}" method="GET">
            <input align="center" type="submit" value="Edit another Note" class="btn btn-info">
        </form>
        <div class="text-center">
            <div>
                <h4 class="ops-dboard-title">Note Info</h4>
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <td>Note ID:</td>
                        <td>{{$note->id}}</td>
                    </tr>
                    <tr>
                        <td>Patient:</td>
                        <td>{{$note->patient->display_name}}</td>
                    </tr>
                    <tr>
                        <td>Author:</td>
                        <td>{{$note->author->display_name}}</td>
                    </tr>
                    <tr>
                        <td>Note type:</td>
                        <td>{{$note->type}}</td>
                    </tr>
                    <td>Date Performed:</td>
                    <td>{{$note->performed_at}}</td>
                </table>
            </div>
            <hr>
            <div>
                <h4 class="ops-dboard-title">Call Info</h4>
                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                    <tr>
                        <td>Call ID:</td>
                        <td>{{$call->id}}</td>
                    </tr>
                    <tr>
                        <td>Call time:</td>
                        <td>{{$call->call_time}}</td>
                    </tr>
                    <tr>
                        <td>Date Performed:</td>
                        <td>{{$call->called_date}}</td>
                    </tr>
                    <tr>
                        <td>Call status:</td>
                        <td>{{$call->status}} @if($call->status == 'reached')
                                (Successful) @elseif($call->status == 'not reached') (Unsuccessful) @endif</td>
                    </tr>
                </table>
            </div>
            <div>
                <form action="{{route('CallsDashboard.edit')}}" method="POST">
                    <br>
                    <div class="form-group">
                        @if (session('msg'))
                            <div class="alert alert-success">
                                {{ session('msg') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <div class="form-group">
                            Change Call status to:
                            <br>
                            <input type="radio" name="status" value="reached" required> Successful<br>
                            <input type="radio" name="status" value="not reached"> Unsuccessful<br>
                        </div>
                        <div class="form-group" id="notify-patient-group" style="display:none">
                            <br>
                            <input type="checkbox" name="notify-patient" value="yes">
                            <label for="notify-patient"> Tick here if you would like patient to be notified by email/sms that
                                nurse tried to contact them</label>
                            <br>
                        </div>
                    </div>
                    <br>
                    <input type="hidden" name="callId" value="{{$call->id}}">
                    <input type="hidden" name="noteId" value="{{$note->id}}">
                    <input align="center" type="submit" value="Submit" class="btn btn-info">
                    {{csrf_field()}}
                    {{ method_field('PATCH') }}
                </form>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('input[name="status"]').change(function (e) {
                    if (e.currentTarget.value === "not reached") {
                        $('#notify-patient-group').show();
                    } else {
                        $('#notify-patient-group').hide();
                    }
                });
            });
        </script>
    @endpush
@endsection
