<div id="consented" class="modal confirm modal-fixed-footer consented_modal">
    <form method="post" id="consented_form" action="{{URL::route('enrollment-center.consented')}}">

        {{ csrf_field() }}

        <div class="modal-content">
            <h4 style="color: #47beab">Awesome! Please confirm patient details:</h4>
            <blockquote style="border-left: 5px solid #26a69a;">
                <span class="consented_title"><b>I.</b></span>
                @if($enrollee->lang == 'ES')
                    <b>Ask patient:</b> ¿Quiere quele llamemos directamente o hay alguien más con el cual quiere quenos
                    pongamos en
                    contacto?
                @else
                    <b>Ask patient:</b> Do you want us to call you directly or is there someone else we should contact?
                @endif
                <br>
                Confirm the patient’s phone number(s) [format: xxx-xxx-xxxx] and select preferred number with radio button:
                <br>
            </blockquote>
            <div class="row">
                <div class="col s6 m4 select-custom">
                    <input name="preferred_phone" type="radio" id="home_radio" value="home"
                           @if($enrollee->home_phone != '') checked @endif/>
                    <label for="home_radio"
                           v-bind:class="{valid: home_is_valid, invalid: home_is_invalid}">@{{home_phone_label}}</label>
                    <input class="input-field" name="home_phone" id="home_phone" v-model="home_phone"
                           v-bind:disabled="disableCall"/>
                </div>
                <div class="col s6 m4 select-custom">
                    <input name="preferred_phone" type="radio" id="cell_radio" value="cell"
                           @if($enrollee->home_phone == '' && $enrollee->cell_phone != '') checked @endif/>
                    <label for="cell_radio"
                           v-bind:class="{valid: cell_is_valid, invalid: cell_is_invalid}">@{{cell_phone_label}}</label>
                    <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                </div>
                <div class="col s6 m4 select-custom">
                    <input name="preferred_phone" type="radio" id="other_radio" value="other"
                           @if($enrollee->home_phone == '' && $enrollee->cell_phone == '' && $enrollee->other_phone != '') checked @endif/>
                    <label for="other_radio"
                           v-bind:class="{valid: other_is_valid, invalid: other_is_invalid}">@{{other_phone_label}}</label>
                    <input class="input-field" name="other_phone" id="other_phone" v-model="other_phone"/>
                </div>
            </div>
            <div class="row">
                <blockquote style="border-left: 5px solid #26a69a;">
                    <span class="consented_title"><b>II.</b></span> Please confirm address and email
                </blockquote>

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
                    <span class="consented_title"><b>III.</b></span> Please confirm the patient's preferred contact days and
                    times, and any other relevant information:
                </blockquote>
                <div class="col s12 m3">
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
                <div class="col s12 m3">
                    <label for="times[]" class="label">Times</label>
                    <select name="times[]" id="times[]" multiple>
                        <option disabled selected>Select Times</option>
                        <option value="10:00-12:00">10AM - Noon</option>
                        <option value="12:00-15:00">Noon - 3PM</option>
                        <option value="15:00-18:00">3PM - 6PM</option>
                    </select>
                </div>
                <div class="col s12 m6 select-custom">
                    <input class="materialize-textarea input-field" id="extra" name="extra"
                           placeholder="Optional additional information" style="margin-bottom: 10px; padding-bottom: 18px;">
                </div>
            </div>

            <blockquote style="border-left: 5px solid #26a69a;">
                <span class="consented_title"><b>IV.</b></span>
                <span style="color: red"><b>TELL PATIENT BEFORE HANGING UP!</b></span><br>
                @if($enrollee->lang == 'ES')
                    Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy llamando
                    @{{practice_phone}}. Por favor, guárdelo para que acepte la llamada cuando suene el teléfono.
                    ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
                @else
                    A Registered Nurse will call you shortly from the same # I’m calling from, @{{practice_phone}}.
                    Please save it so you accept the call when she/he rings. So glad we
                    connected! Have a great day!

                @endif
            </blockquote>

            <input type="hidden" name="status" value="consented">
            <input type="hidden" name="enrollee_id" value="{{$enrollee->id}}">
            <input type="hidden" name="total_time_in_system" v-bind:value="total_time_in_system">
            <input type="hidden" name="time_elapsed" v-bind:value="time_elapsed">

        </div>
        <div class="modal-footer">
            <button id="submit" name="submit" type="submit"
                    class="modal-action waves-effect waves-light btn">Confirm and call next patient
            </button>
            <div v-if="onCall === true" style="text-align: center">
                <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                            class="material-icons left">call_end</i>Hang Up</a>
            </div>
        </div>
    </form>
</div>