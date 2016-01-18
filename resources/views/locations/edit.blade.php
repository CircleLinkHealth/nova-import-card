@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Edit Location {{$location->name}}</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ action('LocationController@index') }}" class="btn btn-danger">Back</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        @include('errors.errors')

                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ URL::route('locations.update', array('id' => $location->id)) }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" value="{{$location->name}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Phone Number</label>
                                <div class="col-md-6">
                                    <input type="tel" class="form-control" name="phone" value="{{$location->phone}}" required">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Address Line 1</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="address_line_1" value="{{$location->address_line_1}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Address Line 2</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="address_line_2" value="{{$location->address_line_2}}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">City</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="city" value="{{$location->city}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">State</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="state" value="{{$location->state}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Postal Code</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="postal_code" value="{{$location->postal_code}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Billing Code</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="billing_code" value="{{$location->billing_code}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Location Code</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="location_code" value="{{$location->location_code}}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Parent</label>
                                <div class="col-md-6">
                                    <select name="parent_id">
                                        <option value="">None</option>
                                        <?php foreach( $locations as $id => $loc ){ ?>
                                            <option value="{{ $id }}"
                                                    <?php
                                                    if($location->parent_id == $id){
                                                        echo 'selected';
                                                    }
                                                    ?>
                                                    >{{ $loc }}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                      Save/Back
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
