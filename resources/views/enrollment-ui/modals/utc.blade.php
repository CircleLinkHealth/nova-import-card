<div id="utc" class="modal confirm modal-fixed-footer">
    <form method="post" id="utc_form" action="{{URL::route('enrollment-center.utc')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Please provide some details:</h4>
            <blockquote style="border-left: 5px solid #26a69a;">
                <b>If Caller Reaches Machine, Leave Voice Message: </b><br>
                Hi this is {{auth()->user()->fullName}} calling on
                behalf of @{{ provider_name }} at @{{ practice_name }}. The doctor[s] have invited you to their new
                personalized care management program. Please give us a call at [number Ambassador calling from on page 2] to learn more. Please note there is
                nothing to worry about, this program just lets the Dr. take better care of you between visits. Again the number is [number Ambassador calling from]
            </blockquote>

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