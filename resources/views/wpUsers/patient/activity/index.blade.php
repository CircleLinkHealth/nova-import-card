@extends('partials.providerUI')
@section('content')
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title">
                    Patient Activity Report
                </div>
                @include('partials.userheader')
                <div class="col-sm-2">
                    <a href="{{ URL::route('patient.note.create', array('patient' => $patient->ID)) }}"
                       class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">+NEW
                        OFFLINE ACTIVITY</a><br>
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.index', ['patientId' => $patient]), 'method' => 'GET', 'class' => 'form-horizontal')) !!}
                <div class="form-group  pull-right" style="margin-top:10px;">
                    <i class="icon icon--date-time"></i>

                    <div class="inline-block">
                        <label for="selectMonth" class="sr-only">Select Month:</label>
                        <select name="selectMonth" id="selectMonth" class="selectpicker" data-width="200px"
                                data-size="10" style="display: none;">
                            <option value="">Select Month</option>
                            @for($i = 0; $i < count($months); $i++)
                                <option value="{{$i+1}}" @if($month_selected == $i+1) {{'selected'}} @endif>{{$months[$i]}}</option>
                            @endfor

                        </select>

                        <div class="inline-block">
                            <label for="selectYear" class="sr-only">Select Year:</label>
                            <select name="selectYear" id="selectYear" class="selectpicker" data-width="100px"
                                    data-size="10" style="display: none;">
                                @foreach($years as $year)
                                    <option value="{{$year}}" selected="selected">{{$year}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" value="Search" name="find" id="find" class="btn btn-primary">Go</button>
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    @if($data)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div><br/>
                        <style>
                            .webix_hcell {
                                background-color: #d2e3ef;
                            }
                        </style>
                        <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                               onclick="obs_alerts_dtable.exportToPDF();">
                        <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                               onclick="obs_alerts_dtable.exportToExcel();">
                    @else
                        <div style="text-align:center;margin:50px;">There are no patient Notes/Offline Activities to
                            display for this month.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@stop