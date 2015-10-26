<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 col-md-offset-1 well well">
            <div class="row">
                <div class="col-sm-8">
                    <h2>Quick Add Patient</h2>
                </div>
                <div class="col-sm-4">
                    <div class="pull-right" style="margin:20px;">
                    </div>
                </div>
            </div>
            <hr />
            {{--{!! Form::open(array('url' => 'test/form/dump', 'class' => 'form-horizontal')) !!}--}}
            {!! Form::open(array('url' => '#', 'class' => 'form-horizontal')) !!}
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1">{!! Form::label('first_name', 'First Name:') !!}</div>
                    <div class="col-xs-3">{!! Form::text('first_name', '', ['class' => 'form-control', 'required']) !!}</div>
                    <div class="col-xs-1">{!! Form::label('last_name', 'Last Name:') !!}</div>
                    <div class="col-xs-3">{!! Form::text('last_name', '', ['class' => 'form-control', 'required']) !!}</div>
                    <div class="col-xs-1">{!! Form::label('email', 'Email Address:') !!}</div>
                    <div class="col-xs-3">{!!Form::input('email', 'email', null, ['class' => 'form-control', 'required'])!!}</div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">

                    <div class="col-xs-1">{!! Form::label('mail', 'Mailing Address:') !!}</div>
                    <div class="col-xs-3">{!! Form::text('description', '', ['class' => 'form-control', 'required']) !!}</div>
                    <div class="col-xs-1">{!! Form::label('mrn', 'MRN#:') !!}</div>
                    <div class="col-xs-3">{!! Form::text('mrn', '', ['class' => 'form-control', 'required']) !!}</div>
                    <div class="col-xs-1">{!! Form::label('CCM', 'CCM Enrollment') !!}</div>
                    <div class="col-xs-1">{!! Form::radio('CCM', 'enrolled', true) !!} Enrolled</div>
                    <div class="col-xs-2">{!! Form::radio('CCM', 'unenrolled') !!} Un-enrolled</div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1">{!! Form::label('date', 'Date Of Birth:') !!}</div>
                    <div class="col-xs-3">{!!Form::input('date', 'date', null, ['class' => 'form-control', 'placeholder' => 'Date', 'required'])!!}</div>
                    <div class="col-xs-1">{!! Form::label('date', 'Preferred Contact Days:') !!}</div>
                    <div class="col-xs-3">{!! Form::select('contact_days[]', $days , null, ['class' => 'form-control', 'multiple', 'data-size' => '10', 'required']) !!}</div>
                    <div class="col-xs-1">{!! Form::label('date', 'Preferred Contact Time:') !!}</div>
                    <div class="col-xs-3">{!! Form::input('time', 'daily_reminder_time', null, ['class' => 'form-control', 'required']) !!}</div>

                </div>
            </div>
            <hr />
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-1">{!! Form::label('date', 'Preferred Office Location:') !!}</div>
                    <div class="col-xs-2">
                        <select class="form-control" name="lead_contact">
                            @foreach($offices as $office)
                                <option value="{{$office}}">{{$office}}</option>
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
                                        <div class="col-xs-1">{!! Form::label('starting', 'Starting:') !!}</div>
                                        <div class="col-xs-2">{!! Form::text('other_problems', '', ['class' => 'form-control']) !!}</div>
                                        <div class="col-xs-1">{!! Form::label('target', 'Target:') !!}</div>
                                        <div class="col-xs-2">{!! Form::text('other_problems', '', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div><hr />
                {!! Form::submit('Save [test only]') !!}
            </div>
        </div>
    </div>
</div>
<script>
    @foreach ($items['Biometrics to Monitor'] as $item)
        $('#{!!$item->items_id!!}').change(function(){
            if(this.checked) {
                $('#{!!$item->items_id . "_initial"!!}'.next().show();
            } else {
                $(this).next().hide();
            }
        });
    @endforeach
</script>