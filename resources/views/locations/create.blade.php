@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Add New Location</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ action('\App\Http\Controllers\LocationController@index') }}" class="btn btn-danger">Back</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('core::partials.errors.errors')

                        <form id="location-form" class="form-horizontal" role="form" method="POST"
                              action="{{ action('\App\Http\Controllers\LocationController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Practice</label>
                                <div class="col-md-6">
                                    <select name="practice_id" class="form-control">
                                        @foreach($practices as $practice)
                                            <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">EMR Direct Address</label>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="emr_direct"
                                           value="{{ old('emr_direct') }}"
                                           placeholder="circlelinkhealth@test.directproject.net">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Phone Number</label>
                                <div class="col-md-6">
                                    <input type="tel" class="form-control" name="phone" value="{{ old('phone') }}"
                                           required placeholder="+12224446666" pattern="^\+?[1-9]\d{1,14}$">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Fax Number</label>
                                <div class="col-md-6">
                                    <input type="tel" class="form-control" name="fax" value="{{ old('fax') }}"
                                           placeholder="+13334445555" pattern="^\+?[1-9]\d{1,14}$">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Address Line 1</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="address_line_1"
                                           value="{{ old('address_line_1') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Address Line 2</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="address_line_2"
                                           value="{{ old('address_line_2') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">City</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="city" value="{{ old('city') }}"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">State</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="state" value="{{ old('state') }}"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">{!! Form::label('timezone', 'Timezone: ') !!}</label>
                                <div class="col-md-6">
                                    {!! Form::select('timezone',
                                    array(
                                    'America/New_York' => 'Eastern Time',
                                    'America/Chicago' => 'Central Time',
                                    'America/Denver' => 'Mountain Time',
                                    'America/Phoenix' => 'Mountain Time (no DST)',
                                    'America/Los_Angeles' => 'Pacific Time',
                                    'America/Anchorage' => 'Alaska Time',
                                    'America/Adak' => 'Hawaii-Aleutian',
                                    'Pacific/Honolulu' => 'Hawaii-Aleutian Time (no DST)',
                                    ),
                                    'America/New_York', ['class' => 'form-control', 'style' => 'width:100%;']) !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Postal Code</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="postal_code"
                                           value="{{ old('postal_code') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Add Location
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
