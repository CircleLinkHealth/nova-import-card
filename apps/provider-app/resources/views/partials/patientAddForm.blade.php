<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 well well">
            <div class="row">
                <div class="col-sm-8">
                    <h2>Quick Add Participant</h2>
                </div>
                <div class="col-sm-4">
                    <div class="pull-right" style="margin:20px;">
                    </div>
                </div>
            </div>
            <hr />
            {!! Form::open(array('url' => 'test/form/dump','method' => 'post', 'class' => 'form-horizontal')) !!}
            {{--{!! Form::open(array('url' => '#', 'class' => 'form-horizontal')) !!}--}}
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1"><label for="first_name">First Name</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="first_name" type="text" value="" id="first_name"></div>
                    <div class="col-xs-1"><label for="last_name">Last Name</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="last_name" type="text" value="" id="last_name"></div>
                    <div class="col-xs-1"><label for="email">Email</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="email" type="email" value="" id="email"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1"><label for="mail">Mailing Address:</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="mail" type="text" value="" id="mail"></div>
                    <div class="col-xs-1"><label for="mrn">MRN#:</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="mrn" type="text" value="" id="mrn"></div>
                    <div class="col-xs-1"><label for="ccm">CCM Enrollment:</label></div>
                    <div class="col-xs-1"><input class="" required="required" name="ccm" type="radio" value="" id="enrolled">Enrolled</div>
                    <div class="col-xs-2"><input class="" required="required" name="ccm" type="radio" value="" id="unenrolled">Un-Enrolled</div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1"><label for="date">Date Of Birth:</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="date" type="date" value="" id="date" placeholder="date"></div>
                    <div class="col-xs-1">{!! Form::label('date', 'Preferred Contact Days:') !!}</div>
                    <div class="col-xs-3">{!! Form::select('contact_days[]', $days , null, ['class' => 'form-control', 'multiple', 'data-size' => '10', 'required']) !!}</div>
                    <div class="col-xs-1"><label for="preferred_contact_time">Preferred Contact Time:</label></div>
                    <div class="col-xs-3"><input class="form-control" required="required" name="preferred_contact_time" type="time" value="" id="preferred_contact_time" placeholder="time"></div>
                </div>
            </div>
            <hr />
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1">{!! Form::label('date', 'Preferred Office Location:') !!}</div>
                    <div class="col-xs-2">
                        <select class="form-control" name="lead_contact">
                            @foreach($offices as $office)
                                <option value="{{$office->id}}">{{$office->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-xs-1">{!! Form::label('billing_provider', 'Billing Provider:') !!}</div>
                        <div class="col-xs-3">
                            <select class="form-control" name="billing_provider">
                                @foreach($providers as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xs-1">{!! Form::label('lead_contact', 'Lead Provider:') !!}</div>
                        <div class="col-xs-3">
                            <select class="form-control" name="lead_contact">
                                @foreach($providers as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-xs-1">{!!Form::label('date', 'Consent Date:') !!}</div>
                    <div class="col-xs-2">{!!Form::input('date', 'consent_date', null, ['class' => 'form-control', 'placeholder' => 'Date', 'required'])!!}</div>
                    <div class="col-xs-1">{!! Form::label('date', 'Send Alerts to:') !!}</div>
                    <div class="col-xs-3">{!! Form::select('alerts[]', $providers , null, ['class' => 'form-control', 'multiple', 'data-size' => '5', 'required']) !!}</div>
                </div>
                <hr />

                <div class="form-group col-xs-4"><h4>Qualifying Chronic Conditions</h4></div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            @foreach ($items['Diagnosis / Problems to Monitor'] as $item)
                                <div class="col-sm-2"><input tabindex="1" type="checkbox" name="problems[]" id="{{$item->items_id}}" value="{{$item->items_id}}">
                                {{$item->items_text}}</div>
                            @endforeach
                        </div>
                    </div>
                    <br />

                        <div class="col-xs-2">{!! Form::label('other_problems', 'Describe Other Conditions (Optional):') !!}</div>
                        <div class="col-xs-5">{!! Form::text('other_problems', '', ['class' => 'form-control']) !!}</div>

                </div><hr />
                <div class="form-group col-xs-4"><h4>Medications to monitor</h4></div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-12">
                            @foreach ($items['Medications to Monitor'] as $item)
                                <div class="col-sm-2"><input tabindex="1" type="checkbox" name="medications[]" id="{{$item->items_id}}" value="{{$item->items_id}}">
                                    {{$item->items_text}}</div>
                            @endforeach
                        </div>
                    </div>
                </div><hr />

                <div class="form-group col-xs-4"><h4>Biometrics to monitor</h4></div>
                <div class="form-group">
                        <div class="col-sm-12">
                            @foreach ($items['Biometrics to Monitor'] as $item)
                                <div class="row">
                                    <div class="col-xs-2"><input tabindex="1" type="checkbox" name="biometrics[]" id="{{$item->items_id}}" value="{{$item->items_id}}"><strong>{{$item->items_text}}</strong></div>
                                    <div id="{{$item->items_id . "_initial"}}">
                                        <div class="col-xs-1">{!! Form::label($item->items_id . '_starting', 'Starting:') !!}</div>
                                        <div class="col-xs-2">{!! Form::text($item->items_id . '_starting', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-1">{!! Form::label($item->items_id . '_target', 'Target:') !!}</div>
                                        <div class="col-xs-2">{!! Form::text($item->items_id . '_target', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>

                                <script type="text/javascript">
                                    function valueChanged()
                                    {

                                        if($("input[id^='item->items_id']").is(":checked"))
                                            $("#{!!$item->items_id . "_initial" !!}").show();
                                        else
                                            $("#{!!$item->items_id . "_initial" !!}").hide();
                                    }
                                </script>

                            @endforeach
                        </div>
                    </div>
                </div><hr />
                {!! Form::submit('Save') !!}
        </div>
        </div>
    </div>
</div>


