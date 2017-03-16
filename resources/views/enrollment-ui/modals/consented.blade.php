<div id="consented" class="modal confirm modal-fixed-footer consented_modal">
    <form method="post" id="consented_form" action="{{URL::route('enrollment-center.consented')}}">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Awesome! Please confirm patient details:</h4>
            <blockquote style="border-left: 5px solid #26a69a;">
                @if($enrollee->lang == 'ES')
                    Ask patient: ¿Quiere quele llamemos directamente o hay alguien más con el cual quiere quenos pongamos en
                    contacto?
                @else
                    <b>Ask patient: Do you want us to call you directly or is there someone else we should contact?</b>
                @endif
                <br>
                Please confirm the patient's preferred phone number (format: XXX-XXX-XXXX):
            </blockquote>
            <div class="row">
                <div class="col s6 m3" style="padding-top: 4px;">
                    <label for="preferred_phone" class="preferred_phone">Preferred Contact Phone</label>
                    <select name="preferred_phone" id="preferred_phone">
                        <option value="home" selected>Home Phone</option>
                        <option value="cell">Cell Phone</option>
                        <option value="other">Other Phone</option>
                    </select>
                </div>
                <div class="col s6 m3 select-custom">
                    <label for="home_phone"
                           v-bind:class="{valid: home_is_valid, invalid: home_is_invalid}">@{{home_phone_label}}</label>
                    <input class="input-field" name="home_phone" id="home_phone" v-model="home_phone"/>
                </div>
                <div class="col s6 m3 select-custom">
                    <label for="cell_phone"
                           v-bind:class="{valid: cell_is_valid, invalid: cell_is_invalid}">@{{cell_phone_label}}</label>
                    <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                </div>
                <div class="col s6 m3 select-custom">
                    <label for="other_phone"
                           v-bind:class="{valid: other_is_valid, invalid: other_is_invalid}">@{{other_phone_label}}</label>
                    <input class="input-field" name="other_phone" id="other_phone" v-model="other_phone"/>
                </div>
                <div class="col s12 m3 select-custom">
                    <label for="address" class="label">Address</label>
                    <input class="input-field" name="address" id="address" v-model="address"/>
                </div>
                <div class="col s12 m2 select-custom">
                    <label for="address_2" class="label">Address Line 2</label>
                    <input class="input-field" name="address_2" id="address_2" v-model="address_2"/>
                </div>
                <div class="col s12 m2 select-custom">
                    <label for="city" class="label">City</label>
                    <input class="input-field" name="city" id="city" v-model="city"/>
                </div>
                <div class="col s12 m1 select-custom">
                    <label for="state" class="label">State</label>
                    <input class="input-field" name="state" id="state" v-model="state"/>
                </div>
                <div class="col s12 m1 select-custom">
                    <label for="zip" class="label">Zip</label>
                    <input class="input-field" name=zip" id="zip" v-model="zip"/>
                </div>
                <div class="col s12 m3 select-custom">
                    <label for="email" class="label">Email</label>
                    <input class="input-field" name="email" id="email" v-model="email"/>
                </div>
            </div>
            <div class="row">
                <blockquote style="border-left: 5px solid #26a69a;">
                    Please confirm the patient's preferred contact days and times:
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
                <blockquote style="border-left: 5px solid #26a69a; margin: 10px 0;">
                    Is there anything else we should know about the patient?
                </blockquote>
                <div class="col s12 m12 select-custom">
                    <input class="materialize-textarea input-field" id="extra" name="extra"
                           placeholder="Optional additional information" style="margin-bottom: 10px">
                </div>
            </div>
            <blockquote style="border-left: 5px solid #26a69a;">

                <b>TELL PATIENT BEFORE HANGING UP!</b><br>
                @if($enrollee->lang == 'ES')
                    Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy llamando
                    [number of practice]. Por favor, guárdelo para que acepte la llamada cuando suene el teléfono.
                    ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
                @else
                    A Registered Nurse will call you shortly from the same # I’m calling from, [number of
                    practice]. Please save it so you accept the call when she/he rings. So glad we
                    connected! Have a great day!

                @endif
            </blockquote>

            <input type="hidden" name="status" value="consented">
            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">
            <input type="hidden" name="time_elapsed" v-bind:value="total_time_in_system">

        </div>
        <div class="modal-footer">
            <button id="submit" name="submit" type="submit"
                    class="modal-action waves-effect waves-light btn">Confirm and Call Next Patient
            </button>
        </div>
    </form>
</div>