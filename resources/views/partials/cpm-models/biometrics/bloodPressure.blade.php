<div class="collapse in" id="collapse-{{ $section->name }}-{{$i}}{{$item->id}}">
    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Starting BP<br>
            <input class="form-control" name="biometrics[bloodPressure][starting]"
                   value="{{$biometrics->bloodPressure->starting}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Target BP<br>
            <input class="form-control" name="biometrics[bloodPressure][target]"
                   value="{{$biometrics->bloodPressure->target}}" placeholder=""
                   type="text">
        </div>
    </div>
    <br>
    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Systolic High Alert<br>
            <input class="form-control" name="biometrics[bloodPressure][systolic_high_alert]"
                   value="{{$biometrics->bloodPressure->systolic_high_alert}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Systolic Low Alert<br>
            <input class="form-control" name="biometrics[bloodPressure][systolic_low_alert]"
                   value="{{$biometrics->bloodPressure->systolic_low_alert}}"
                   placeholder="" type="text">
        </div>
    </div>
    <br>
    <div class="row item-row" style="">

        <div class="col-sm-6 cp-item-child" style="">
            Diastolic High Alert<br>
            <input class="form-control" name="biometrics[bloodPressure][diastolic_high_alert]"
                   value="{{$biometrics->bloodPressure->diastolic_high_alert}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Diastolic Low Alert<br>
            <input class="form-control" name="biometrics[bloodPressure][diastolic_low_alert]"
                   value="{{$biometrics->bloodPressure->diastolic_low_alert}}"
                   placeholder="" type="text">
        </div>
    </div>
    <br>
</div>