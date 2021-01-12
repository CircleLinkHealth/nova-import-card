<div class="form-group">
    <div class="row">
        <div class="col-xs-2">{!! Form::label('hourly_rate', 'Hourly Rate:') !!}</div>
        <div class="col-xs-10">{!! Form::text('hourly_rate', optional($nurseInfo)->hourly_rate, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
    </div>

    <div class="row" style="padding: 10px 0px;">
        <div class="col-xs-2">{!! Form::label('high_rate', 'Var. High Rate:') !!}</div>
        <div class="col-xs-3">{!! Form::text('high_rate', optional($nurseInfo)->high_rate, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
        <div class="col-xs-2">{!! Form::label('high_rate_2', 'Var. High Rate 2:') !!}</div>
        <div class="col-xs-3">{!! Form::text('high_rate_2', optional($nurseInfo)->high_rate_2, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
        <div class="col-xs-2">{!! Form::label('high_rate_3', 'Var. High Rate 3:') !!}</div>
        <div class="col-xs-3">{!! Form::text('high_rate_3', optional($nurseInfo)->high_rate_3, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
        <div class="col-xs-2">{!! Form::label('low_rate', 'Var. Low Rate:') !!}</div>
        <div class="col-xs-3">{!! Form::text('low_rate', optional($nurseInfo)->low_rate, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
    </div>

    <div class="row" style="padding: 10px 0px;">
        <div class="col-xs-2">{!! Form::label('visit_fee', 'Visit Fee:') !!}</div>
        <div class="col-xs-3">{!! Form::text('visit_fee', optional($nurseInfo)->visit_fee, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
        <div class="col-xs-2">{!! Form::label('visit_fee_2', 'Visit Fee 2:') !!}</div>
        <div class="col-xs-3">{!! Form::text('visit_fee_2', optional($nurseInfo)->visit_fee_2, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
        <div class="col-xs-2">{!! Form::label('visit_fee_3', 'Visit Fee 3:') !!}</div>
        <div class="col-xs-3">{!! Form::text('visit_fee_3', optional($nurseInfo)->visit_fee_3, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
    </div>

    <div class="row">
        <div class="col-xs-2">{!! Form::label('status', 'Status') !!}</div>
        <div class="col-xs-4">{!! Form::select('status', array('inactive' => 'Inactive', 'active' => 'Active'), optional($nurseInfo)->status, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
    </div>

    <div class="row" style="margin-top:10px;">
        <div class="col-xs-2">{!! Form::label('spanish', 'Spanish') !!}</div>
        <div class="col-xs-4">{!! Form::select('spanish', array('0' => 'No', '1' => 'Yes'), optional($nurseInfo)->spanish, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
        <div class="col-xs-2"></div>
        <div class="col-xs-4"></div>
    </div>
</div>

