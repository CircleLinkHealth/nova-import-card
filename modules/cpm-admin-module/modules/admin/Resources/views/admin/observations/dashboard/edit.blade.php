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
        <h3 align="center">Observation Details</h3>
    </div>
        <div class="container">
            <div>
                <form action="{{route('observations-dashboard.list')}}" method="GET">
                    <input type="hidden" name="userId" value="{{$observation->user_id}}">
                    <input align="left" type="submit" value="Return to list" class="btn btn-info">
                    <br>
                </form>
                    <form action="{{route('observations-dashboard.update')}}" onsubmit="return confirmObservationSubmit()" method="POST">
                        {!! method_field('patch') !!}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                            </div>
                            <br>
                            <div class="panel-body">
                                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                                    <p><strong>Editable Data:</strong> (warning: the following data will be stored in the database exactly as edited here)</p>
                                    <tr>
                                        <th>Observation Key</th>
                                        <td>
                                            <textarea  name="obs_key">{{$observation->obs_key}}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Value</th>
                                        <td>
                                            <textarea name="obs_value">{{$observation->obs_value}}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Method</th>
                                        <td>
                                            <textarea name="obs_method">{{$observation->obs_method}}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Message Id</th>
                                        <td>
                                            <textarea name="obs_message_id">{{$observation->obs_message_id}}</textarea>
                                        </td>
                                    </tr>


                                </table>
                                <table class="table table-striped table-bordered table-curved table-condensed table-hover">
                                    <p><strong>Additional Information:</strong></p>
                                    <tr>
                                        <th>Date</th>
                                        <td>{{$observation->obs_date}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>User </th>
                                        <td>{{$observation->user->display_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Practice Id</th>
                                        <td>{{$observation->program_id}}</td>
                                    </tr>
                                    <tr>
                                        <th>Comment Id </th>
                                        @if($observation->comment)
                                            <td>{{$observation->comment->id}}</td>
                                            @else <td></td>
                                        @endif
                                    </tr>
                                </table>
                            </div>
                            <div>

                        <div class="form-group">
                            <input type="hidden" name="obsId" value="{{$observation->id}}">
                            <input type="submit" value="Submit Changes" class="btn btn-info">
                            @if (session('msg'))
                                <div class="alert alert-success">
                                    {{ session('msg') }}
                                </div>
                            @endif
                        </div>
                        {{csrf_field()}}
                            </div>
                        </div>
                    </form>

                    @push('scripts')
                        <script>
                            function confirmObservationSubmit() {
                                return confirm('Are you sure you want to change this observation?')
                            }
                        </script>
                    @endpush
            </div>
        </div>
@endsection