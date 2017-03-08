<div id="rejected" class="modal confirm modal-fixed-footer">
    <form method="post" id="rejected_form" action="{{URL::route('enrollment-center.rejected')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Please provide some details:</h4>

            <div class="row">
                <div class="col s12 m12">
                    <label for="reason" class="label">What reason did the Patient convey?</label>
                    <select name="reason" id="reason" required>
                        <option value="Worried about co-pay" >Worried about co-pay</option>
                        <option value="Doesn’t trust medicare" >Doesn’t trust medicare</option>
                        <option value="Doesn’t need help with Health" >Doesn’t need help with Health</option>
                        <option value="other" >Other...</option>
                    </select>
                </div>

                <div class="col s6 m12 select-custom">
                    <label for="reason_other" class="label">If you selected other, please specify:</label>
                    <input class="input-field" name="reason_other" id="reason_other"/>
                </div>

            </div>

            <input type="hidden" name="status" value="rejected">
            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">

            <div class="modal-footer" style="padding-right: 60px">
                <button id="submit" name="submit" type="submit" v-on:click="utc_submit"
                        class="modal-action waves-effect waves-light btn">Call Next Patient
                </button>
            </div>
        </div>
    </form>
</div>

