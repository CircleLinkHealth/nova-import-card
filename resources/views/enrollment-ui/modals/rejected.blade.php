<div id="rejected" class="modal confirm modal-fixed-footer">
    <form method="post" id="rejected_form" action="{{route('enrollment-center.rejected')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Please provide some details:</h4>

            <div class="row">
                <div class="col s12 m12">
                    <label for="reason" class="label">What reason did the Patient convey?</label>
                    <select name="reason" id="reason" required>
                        <option value="Worried about co-pay">Worried about co-pay</option>
                        <option value="Doesn’t trust medicare">Doesn’t trust medicare</option>
                        <option value="Doesn’t need help with Health">Doesn’t need help with Health</option>
                        <option value="other">Other...</option>
                    </select>
                </div>

                <div class="col s6 m12 select-custom">
                    <label for="reason_other" class="label">If you selected other, please specify:</label>
                    <input class="input-field" name="reason_other" id="reason_other"/>
                </div>

                <div v-if="isSoftDecline" class="col s6 m12 select-custom">
                    <label for="soft_decline_callback" class="label">Patient Requests Callback On:</label>
                    <input type="date" name="soft_decline_callback" id="soft_decline_callback">
                    <input class="input-field" name="reason_other" id="reason_other"/>
                    <input type="hidden" name="status" value="soft_rejected">
                </div>
                <div v-else>
                    <input type="hidden" name="status" value="rejected">
                </div>

            </div>


            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">
            <input type="hidden" name="total_time_in_system" v-bind:value="total_time_in_system">
            <input type="hidden" name="time_elapsed" v-bind:value="time_elapsed">

            <div class="modal-footer" style="padding-right: 60px">
                <button id="submit" name="submit" type="submit"
                        class="modal-action waves-effect waves-light btn">Call Next Patient
                </button>
                <div v-if="onCall === true" style="text-align: center">
                    <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                class="material-icons left">call_end</i>Hang Up</a>
                </div>
            </div>
        </div>
    </form>
</div>

