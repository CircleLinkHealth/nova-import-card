<div id="utc" class="modal confirm modal-fixed-footer">
    <form method="post" id="utc_form" action="{{URL::route('enrollment-center.utc')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Please provide some details:</h4>

            <div class="row">
                <div class="col s12 m12">
                    <label for="reason" class="label">Reason:</label>
                    <select name="reason" id="reason" required>
                        <option value="voicemail">Left A Voicemail</option>
                        <option value="disconnected">Disconnected Number</option>
                        <option value="requested callback">Requested Call At Other Time</option>
                        <option value="other">Other...</option>
                    </select>
                </div>

                <div class="col s6 m12 select-custom">
                    <label for="reason_other" class="label">If you selected other, please specify:</label>
                    <input class="input-field" name="reason_other" id="reason_other"/>
                </div>

            </div>

            <input type="hidden" name="status" value="utc">
            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">
            <input type="hidden" name="time_elapsed" v-bind:value="total_time_in_system">


            <div class="modal-footer" style="padding-right: 60px">
                <button id="submit" name="submit" type="submit"
                        class="modal-action waves-effect waves-light btn">Call Next Patient
                </button>
            </div>
        </div>
    </form>
</div>