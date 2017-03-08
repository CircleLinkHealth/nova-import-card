<div id="consented" class="modal confirm modal-fixed-footer">
    <form method="post" id="consented_form" action="{{URL::route('enrollment-center.store')}}"
          class="">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Awesome! Please confirm patient details.</h4>

            <div class="row">
                <blockquote style="border-left: 5px solid #26a69a;">
                    Please confirm the patient's preferred contact details:
                </blockquote>
                <div class="col s6 m3 select-custom">
                    <label for="phone" class="label">Home Phone</label>
                    <input class="input-field" name="home_phone" id="phone" v-model="home_phone"/>
                </div>
                <div class="col s6 m3 select-custom">
                    <label for="address" class="label">Cell Phone</label>
                    <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                </div>
                <div class="col s12 m6 select-custom">
                    <label for="address" class="label">Preferred Address</label>
                    <input class="input-field" name="address" id="address" v-model="address"/>

                </div>

            </div>
            <div class="row">
                <blockquote style="border-left: 5px solid #26a69a;">
                    Please confirm the patient's preferred contact details:
                </blockquote>
                <div class="col s12 m6">
                    <label for="days[]" class="label">Day</label>
                    <select name="days[]" id="days[]" multiple>
                        <option disabled selected>Select Days</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                    </select>
                </div>
                <div class="col s12 m6">
                    <label for="times[]" class="label">Times</label>
                    <select name="times[]" id="times[]" multiple>
                        <option disabled selected>Select Times</option>
                        <option value="10:00-12:00">10AM - Noon</option>
                        <option value="12:00-15:00">Noon - 3PM</option>
                        <option value="15:00-18:00">3PM - 6PM</option>
                    </select>
                </div>
            </div>
            <div class="row input-field">
                <blockquote style="border-left: 5px solid #26a69a;">
                    Is there anything else we should know about the patient?
                </blockquote>
                <div class="col s12 m6 select-custom">
                            <textarea class="materialize-textarea input-field" id="extra" name="extra"
                                      placeholder="Optional additional information"></textarea>
                </div>
            </div>

            <input type="hidden" name="status" value="consented">
            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">

        </div>
        <div class="modal-footer">
            <button id="submit" name="submit" type="submit" v-on:click="consent_submit"
                    class="modal-action waves-effect waves-light btn">Confirm and Call Next Patient
            </button>
        </div>
    </form>
</div>