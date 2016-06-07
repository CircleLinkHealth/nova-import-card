<div class="collapse in" id="collapse-{{ $section->name }}-{{$i}}{{$item->id}}">
    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Starting A1c<br>
            <input class="form-control" name="biometrics[bloodSugar][starting_a1c]"
                   value="{{$biometrics->bloodSugar->starting_a1c}}"
                   placeholder="" type="text">
        </div>
    </div>
    <br>

    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Starting BS<br>
            <input class="form-control" name="biometrics[bloodSugar][starting]"
                   value="{{$biometrics->bloodSugar->starting}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Target BS<br>
            <input class="form-control" name="biometrics[bloodSugar][target]"
                   value="{{$biometrics->bloodSugar->target}}"
                   placeholder=""
                   type="text">
        </div>


    </div>
    <br>
    <div class="row item-row" style="">

        <div class="col-sm-6 cp-item-child" style="">
            BS Low Alert<br>
            <input class="form-control" name="biometrics[bloodSugar][low_alert]"
                   value="{{$biometrics->bloodSugar->low_alert}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            BS High Alert<br>
            <input class="form-control" name="biometrics[bloodSugar][high_alert]"
                   value="{{$biometrics->bloodSugar->high_alert}}"
                   placeholder="" type="text">
        </div>
    </div>
    <br>
</div>