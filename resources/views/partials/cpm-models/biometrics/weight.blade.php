<div class="collapse in" id="collapse-{{ $section->name }}-{{$i}}{{$item->id}}">
    <div class="row item-row" style="">
        <div class="col-sm-6 cp-item-child" style="">
            Starting<br>
            <input class="form-control" name="biometrics[weight][starting]" value="{{$biometrics->weight->starting}}"
                   placeholder="" type="text">
        </div>

        <div class="col-sm-6 cp-item-child" style="">
            Target<br>
            <input class="form-control" name="biometrics[weight][target]" value="{{$biometrics->weight->target}}"
                   placeholder=""
                   type="text">
        </div>
    </div>
    <br>


    <div class="row">
        <div class="form-group">
            <div class="form-item col-sm-12">
                <div class="checkbox text-medium-big">
                    <div class="radio-inline">
                        <input id="monitor_changes_for_chf" name="biometrics[weight][monitor_changes_for_chf]"
                               value="1"
                               class="itemTrigger"
                               type="checkbox"
                        @if($biometrics->weight->monitor_changes_for_chf){{ 'checked=checked'}}@endif>
                        <label for="monitor_changes_for_chf">
                            <span></span>Monitor Weight Changes for CHF
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
</div>