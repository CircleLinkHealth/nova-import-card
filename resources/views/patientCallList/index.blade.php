@extends('partials.providerUI')

@section('title', 'Patient Listing')
@section('activity', '')

@section('content')

    <script type="text/javascript" src="{{ asset('/js/admin/reports/patientCallManagement.js') }}"></script>
    <div class="">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Patient Call List</h1>
                        <p>My assigned scheduled calls in order by priority:</p>
                    </div>
                </div>
                <div class="">
                    <div class="">
                        @include('errors.errors')
                        @include('errors.messages')

                        {!! Form::open(array('url' => URL::route('patientCallList.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <div id="filters" class="" style="margin:40px 0px;">
                            <h3>Filters</h3>
                                <div class="form-group">
                                    <div id="dtBox"></div>
                                    <label for="date" class="col-sm-2 control-label">Date: </label>
                                    <div class="col-sm-10">
                                        <input id="date" class="form-control pull-right" name="date" type="input" value="{{ (old('date') ? old('date') : ($date ? $date : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-10" style="margin-top:10px;">
                                        <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-sort"></i> Apply Filter</button>
                                    </div>
                                </div>
                        </div>
                        </form>

                        <h3>Scheduled Calls</h3>
                        <table class="table table-striped" style="">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>DOB</th>
                                <th>Date</th>
                                <th>Contact Window Start</th>
                                <th>Contact Window End</th>
                                <th>Call Center Status</th>
                                <th>Status</th>
                                <th>Last Date called</th>
                                <th>CCM Time to date</th>
                                <th># success</th>
                                <th>Provider</th>
                                <th>Program</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($calls) > 0)
                                @foreach($calls as $call)
                                    <tr>
                                        <td><input type="checkbox" name="calls[]" value="{{ $call->id }}"></td>
                                        <td>
                                            @if($call->outboundUser)
                                                {{ $call->outboundUser->display_name }}
                                            @else
                                                <em style="color:red;">unassigned</em>
                                            @endif
                                        </td>
                                        <td>{{ $call->inbound_cpm_id }}</td>
                                        <td>-</td>
                                        <td>{{ $call->call_date }}</td>
                                        <td>{{ $call->window_start }}</td>
                                        <td>{{ $call->window_end }}</td>
                                        <td>-</td>
                                        <td>{{ $call->status }}</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td class="text-right">
                                            @if(Entrust::can('users-edit-all'))
                                                <a href="{{ URL::route('patientCallList.index', array('id' => $call->id)) }}" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Complete call</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="7">No calls found</td></tr>
                            @endif
                            </tbody>
                        </table>
                        </form>
                        {{ $calls->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>

@stop