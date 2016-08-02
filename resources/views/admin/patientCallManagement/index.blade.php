@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/admin/reports/patientCallManagement.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Patient Call Management</h1>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Manage Patient Calls</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        @include('errors.messages')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        </div>



                        <a class="btn btn-info panel-title" data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">Toggle Filters</a><br /><br />
                        <div id="collapseFilter" class="panel-collapse collapse">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-2"><label for="date">Date:</label></div><div id="dtBox"></div>
                                    <div class="col-xs-4"><input id="date" class="form-control" name="date" type="input" value="{{ (old('date') ? old('date') : ($date->format('Y-m-d') ? $date->format('Y-m-d') : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('date') }}</span></div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-2"><label for="filterNurse">Nurse:</label></div>
                                    <div class="col-xs-4">{!! Form::select('filterNurse', $nurses, $filterNurse, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                                </div>
                            </div>
                            <div class="row" style="margin-top:50px;">
                                <div class="col-sm-12">
                                    <div class="" style="text-align:center;">
                                        {!! Form::hidden('action', 'filter') !!}
                                        <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-sort"></i> Apply Filters</button>
                                        <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-refresh"></i> Reset Filters</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {!! Form::open(array('url' => URL::route('admin.patientCallManagement.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        @if(Entrust::can('users-edit-all'))
                            Selected Call Actions:
                            <select name="action">
                                <option value="scramble">Assign Nurse:</option>
                            </select>
                            <button type="submit" value="Submit" class="btn btn-primary btn-xs" style="margin-left:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i> Perform Action</button>
                        @endif
                        <table class="table table-striped">
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
                                                <a href="{{ URL::route('admin.patientCallManagement.edit', array('id' => $call->id)) }}" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a>
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