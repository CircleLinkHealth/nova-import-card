<div class="collapse in" id="collapse-{{ $section->name }}-{{$i}}{{$item->id}}">
    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Starting Count<br>
            <input class="form-control" name="biometrics[smoking][starting]" value="{{$biometrics->smoking->starting}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Target Count<br>
            <input class="form-control" name="biometrics[smoking][target]" value="{{$biometrics->smoking->target}}" placeholder=""
                   type="text">
        </div>
    </div>
    <br>
</div>