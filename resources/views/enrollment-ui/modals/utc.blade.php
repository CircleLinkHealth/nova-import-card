<div id="utc" class="modal confirm modal-fixed-footer">
    <form method="post" id="consented_form" action="{{URL::route('enrollment-center.store')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Please provide some details.</h4>

            <div class="row">
                <div class="col s12 m6">
                    <label for="reason" class="label">Day</label>
                    <select name="reason" id="reason">
                        <option disabled>Select Days</option>
                        <option value="disconnected" >Disconnected Number</option>
                        <option value="voicemail" >Left A Voicemail</option>
                        <option value="requested callback" >Requested Call At Other Time</option>
                        <option value="other" >Other...</option>
                    </select>
                </div>

                <span>@{{utc_other}}</span>

                <div v-if="show_utc_other === true" class="col s6 m3 select-custom">
                    <label for="reason_other" class="label">Cell Phone</label>
                    <input class="input-field" name="reason_other" id="reason_other"/>
                </div>

            </div>
            <div class="modal-footer">
                <button id="submit" name="submit" type="submit" v-on:click="utc_submit"
                        class="modal-action waves-effect waves-light btn">Call Next Patient
                </button>
            </div>
        </div>
    </form>
</div>