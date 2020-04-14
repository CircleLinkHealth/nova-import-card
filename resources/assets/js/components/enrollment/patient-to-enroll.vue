<template>
    <div>
        <div v-if="patient_data.hasOwnProperty('enrollable')">
            <div class="side-nav fixed">
                <div style="height: 150%; overflow-y: hidden">
                    <div class="row">
                        <div class="col s6">
                            <div class="card">
                                <div class="card-content" style="text-align: center">
                                    <div style="color: #6d96c5" class="counter">
                                        {{report.total_calls ? report.total_calls : 0}}
                                    </div>
                                    <div class="card-subtitle">
                                        Total Calls
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col s6">
                            <div class="card">
                                <div class="card-content" style="text-align: center">
                                    <div style="color: #9fd05f" class="counter">
                                        {{report.no_enrolled ? report.no_enrolled : 0}}
                                    </div>
                                    <div class="card-subtitle">
                                        Enrolled
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content" style="text-align: center">
                                    <div style="color: #6d96c5" class="counter">
                                        {{formatted_total_time_in_system}}
                                    </div>
                                    <div class="card-subtitle">
                                        Time worked
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <ul>
                                        <li class="sidebar-demo-list"><span :title="name"><b>Name:</b> {{name}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="lang"><b>Language:</b> {{lang}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span :title="practice_name"><b>Practice Name:</b> {{practice_name}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span :title="provider_name_for_side_bar"><b>Provider Name:</b> {{provider_name_for_side_bar}}</span>
                                        </li>
                                        <li v-if="provider_pronunciation_exists" class="sidebar-demo-list"><span
                                                :title="provider_pronunciation"><b>Provider Pronunciation:</b> {{provider_pronunciation}}</span>
                                        </li>
                                        <li v-if="provider_sex_exists" class="sidebar-demo-list"><span
                                                :title="provider_sex"><b>Provider Sex:</b> {{provider_sex}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="providerPhone"><b>Provider Phone:</b> {{providerPhone}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="last_office_visit_at"><b>Last Office Visit:</b> {{last_office_visit_at}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="last_attempt_at"><b>Last Attempt:</b> {{last_attempt_at}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="attempt_count"><b>Attempt Count:</b> {{attempt_count}}</span>
                                        </li>
                                        <li class="sidebar-demo-list"><span> </span>
                                        </li>
                                        <li class="sidebar-demo-list"><span
                                                :title="address"><b>Address:</b> {{address}}</span>
                                        </li>
                                        <li v-if="address_2_exists" class="sidebar-demo-list"><span
                                                :title="address_2"><b>2nd Address:</b> {{address_2}}</span>
                                        </li>
                                        <li v-if="home_phone_exists" class="sidebar-demo-list"><span
                                                :title="home_phone"><b>Home Phone:</b> {{home_phone}}</span>
                                        </li>
                                        <li v-if="cell_phone_exists" class="sidebar-demo-list"><span
                                                :title="cell_phone"><b>Cell Phone:</b> {{cell_phone}}</span>
                                        </li>
                                        <li v-if="other_phone_exists" class="sidebar-demo-list"><span
                                                :title="other_phone"><b>Other Phone:</b> {{other_phone}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <p style="text-align: center; padding-bottom: 10px"><strong>Suggested Family
                                        Members</strong></p>
                                    <div v-if="this.family_loading">
                                        <loader style="margin-left: 45%"/>
                                    </div>
                                    <div v-else>
                                        <div v-if="showBanner" class="banner-class">{{this.bannerText}}</div>
                                        <div v-else>
                                            <div v-if="suggested_family_members_exist">
                                                <p style="font-weight: lighter; padding-left: 15px">Check to confirm
                                                    family
                                                    member(s):</p>
                                                <hr>
                                                <div class="scrollable-list">

                                                    <div v-for="member in suggested_family_members"
                                                         class="sidebar-demo-list"
                                                         style="height: auto !important; white-space: initial">
                                                        <label>
                                                            <input type="checkbox" :value="member.id"
                                                                   style="position: relative"
                                                                   v-model="confirmed_family_members"
                                                            >
                                                            <span>{{member.first_name}} {{member.last_name}}</span>
                                                            <div style="padding-left: 10px">
                                                                <div><strong>Addresses:</strong>{{member.addresses.value}}
                                                                </div>
                                                                <div><strong>Phones:</strong>{{member.phones.value}}
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <p style="font-weight: lighter; padding-left: 15px">No suggested family
                                                    members found.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <div class="row">

                                        <div v-if="callError">
                                            <blockquote>Call Status: {{ callError }}</blockquote>
                                        </div>

                                        <div v-if="onCall === true" style="text-align: center">

                                            <blockquote>Call Status: {{ this.callStatus }}</blockquote>
                                            <a v-on:click="hangUp" class="waves-effect waves-light btn"
                                               style="background: red"><i
                                                    class="material-icons left">call_end</i>Hang Up</a>
                                        </div>
                                        <div v-else style="text-align: center">
                                            <div v-if="home_phone !== ''" class="col s4">

                                                <div class="waves-effect waves-light btn call-button"
                                                     v-on:click="call(home_phone, 'Home')">
                                                    <i class="material-icons">phone</i>
                                                </div>
                                                <div>
                                                    Home
                                                </div>

                                            </div>
                                            <div v-if="cell_phone !== ''" class="col s4">

                                                <div class="waves-effect waves-light btn call-button"
                                                     v-on:click="call(cell_phone, 'Cell')">
                                                    <i class="material-icons">phone</i>

                                                </div>
                                                <div>
                                                    Cell
                                                </div>

                                            </div>
                                            <div v-if="other_phone !== ''" class="col s4">

                                                <div class="waves-effect waves-light btn call-button"
                                                     v-on:click="call(other_phone, 'Other')">
                                                    <i class="material-icons">phone</i>

                                                </div>

                                                <div>
                                                    Other
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col s12">
                            <div class="card">
                                <div class="card-content">
                                    <ul class="action-buttons">
                                        <li>
                                            <a class="waves-effect waves-light btn modal-trigger" href="#consented">
                                                Consented
                                            </a>
                                        </li>
                                        <li>
                                            <a class="waves-effect waves-light btn modal-trigger" href="#utc"
                                               style="background: #ecb70e">
                                                Unavailable
                                            </a>
                                        </li>
                                        <li>
                                            <a class="waves-effect waves-light btn modal-trigger" href="#rejected"
                                               style="background: red;">
                                                Hard Declined
                                            </a>
                                        </li>
                                        <li>
                                            <a class="waves-effect waves-light btn modal-trigger" href="#rejected"
                                               v-on:click="softReject()"
                                               style="background: #ff0000c2;">
                                                Soft Declined
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div style="margin-left: 26%;">

                <div class="padding-top-5" style="font-size: 16px;">

                    <blockquote v-if="last_call_outcome !== ''">
                        Last Call Outcome: {{ last_call_outcome }}
                        <span v-if="last_call_outcome_reason !== ''">
                        <br/>
                        Last Call Comment: {{ last_call_outcome_reason }}
                    </span>
                    </blockquote>

                    <div class="enrollment-script font-size-20">
                        <p v-html="care_ambassador_script"></p>
                    </div>
                </div>

                <div style="padding: 10px; margin-bottom: 15px"></div>
                <div style="text-align: center">

                </div>
            </div>

            <!-- MODALS -->

            <!-- Success / Patient Consented -->
            <div id="consented" class="modal confirm modal-fixed-footer consented_modal">
                <form method="post" id="consented_form" :action="consentedUrl" v-on:submit="handleSubmit($event, consentedUrl)">

                    <input type="hidden" name="_token" :value="csrf">

                    <div class="modal-content">
                        <h4 style="color: #47beab">Awesome! Please confirm patient details:</h4>
                        <blockquote style="border-left: 5px solid #26a69a;">
                            <span class="consented_title"><b>I.</b></span>
                            <span>Ask the patient:</span>
                            <div class="font-size-20">
                                <template v-if="lang === 'ES'">
                                    ¿Quiere quele llamemos directamente o hay alguien más con el cual quiere quenos
                                    pongamos
                                    en
                                    contacto?
                                </template>
                                <template v-else>
                                    Is this the best number for you to be reached?
                                </template>
                            </div>
                            <br>
                            <span><strong>Please enter any unknown phone numbers and select the patient's preferred phone number to receive care management calls:</strong></span>
                        </blockquote>
                        <div class="row">
                            <div class="col s6 m3 select-custom">
                                <label for="home_radio"
                                       :class="{valid: home_is_valid, invalid: home_is_invalid}">
                                    <input class="with-gap" v-model="preferred_phone" name="preferred_phone"
                                           type="radio"
                                           id="home_radio" value="home"
                                           :checked="home_phone != ''"/>
                                    <span class="phone-label">{{home_phone_label}}</span>

                                </label>
                                <input class="input-field" name="home_phone" id="home_phone" v-model="home_phone"/>
                            </div>
                            <div class="col s6 m3 select-custom">

                                <label for="cell_radio"
                                       :class="{valid: cell_is_valid, invalid: cell_is_invalid}">
                                    <input class="with-gap" v-model="preferred_phone" name="preferred_phone"
                                           type="radio"
                                           id="cell_radio" value="cell"
                                           :checked="home_phone == '' && cell_phone != ''"/>
                                    <span class="phone-label">{{cell_phone_label}}</span></label>
                                <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                            </div>
                            <div class="col s6 m3 select-custom">

                                <label for="other_radio"
                                       :class="{valid: other_is_valid, invalid: other_is_invalid}">
                                    <input class="with-gap" v-model="preferred_phone" name="preferred_phone"
                                           type="radio"
                                           id="other_radio" value="other"
                                           :checked="home_phone == '' && cell_phone == '' && other_phone != ''"/>
                                    <span class="phone-label">{{other_phone_label}}</span>
                                </label>
                                <input class="input-field" name="other_phone" id="other_phone" v-model="other_phone"/>
                            </div>
                            <div class="col s6 m3 select-custom">
                                <label for="agent_radio"
                                       :class="{valid: agent_is_valid, invalid: agent_is_invalid}">
                                    <input class="with-gap" v-model="preferred_phone" name="preferred_phone"
                                           type="radio"
                                           id="agent_radio" value="agent"
                                           :checked="home_phone == '' && cell_phone == '' && other_phone != ''"/>
                                    <span class="phone-label">{{agent_phone_label}}</span>
                                </label>
                                <input class="input-field" name="agent_phone" id="agent_phone" v-model="agent_phone"/>
                            </div>
                        </div>
                        <div v-if="preferred_phone == 'agent' " class="row">
                            <blockquote style="border-left: 5px solid #26a69a;"><b>Please fill out alternative contact
                                person's
                                details</b></blockquote>
                            <div class="col s6 m4">
                                <label for="agent_name" class="label">Alternative Contact Person's Name</label>
                                <input class="input-field" name="agent_name" id="agent_name" v-model="agent_name"/>
                            </div>
                            <div class="col s6 m4">
                                <label for="agent_email" class="label">Alternative Contact Person's Email</label>
                                <input class="input-field" name="agent_email" id="agent_email" v-model="agent_email"/>
                            </div>
                            <div class="col s6 m4">
                                <label for="agent_relationship" class="label">Alternative Contact Person's Relationship
                                    to
                                    the
                                    Patient</label>
                                <input class="input-field" name="agent_relationship" id="agent_relationship"
                                       v-model="agent_relationship"/>
                            </div>
                        </div>
                        <div class="row">
                            <blockquote style="border-left: 5px solid #26a69a;">
                                <span class="consented_title"><b>II.</b></span> Please confirm the patient’s mailing
                                address
                                and email address:
                            </blockquote>

                            <div class="col s12 m3 select-custom">
                                <label for="address" class="label">Address Line 1</label>
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
                                <input class="input-field" name="zip" id="zip" v-model="zip"/>
                            </div>
                            <div class="col s12 m3 select-custom">
                                <label for="email" class="label">Email</label>
                                <input class="input-field" name="email" id="email" v-model="email"/>
                            </div>
                        </div>
                        <div class="row">
                            <blockquote style="border-left: 5px solid #26a69a;">
                                <span class="consented_title"><b>III.</b></span> Please confirm the patient's preferred
                                contact days
                                and
                                times, and any other relevant information:
                            </blockquote>
                            <div class="col s12 m3">
                                <label for="days[]" class="label">Day</label>
                                <select class="do-not-close" v-model="days" name="days[]" id="days[]" @change="setDays"multiple>
                                    <option disabled selected>Days:</option>
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="all">Any day</option>
                                </select>
                            </div>
                            <div class="col s12 m3">
                                <label for="times[]" class="label">Times</label>
                                <select v-model="times" class="do-not-close" name="times[]" id="times[]">
                                    <option disabled selected>Times:</option>
                                    <option value="09:00-12:00">9AM - Noon</option>
                                    <option value="12:00-15:00">Noon - 3PM</option>
                                    <option value="15:00-18:00">3PM - 6PM</option>
                                </select>
                            </div>
                            <div class="col s12 m6 select-custom">
                                <label for="extra" class="label">Optional additional information</label>
                                <input class="input-field" name="extra" id="extra"/>
                            </div>
                        </div>

                        <blockquote style="border-left: 5px solid #26a69a;">
                            <span class="consented_title"><b>IV.</b></span>
                            <span style="color: red"><b>TELL PATIENT BEFORE HANGING UP!</b></span><br>
                            <div class="font-size-20">
                                <template v-if="lang === 'ES'">
                                    Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy
                                    llamando
                                    {{practice_phone}}. Por favor, guárdelo para que acepte la llamada cuando suene el
                                    teléfono.
                                    ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
                                </template>
                                <template v-else>
                                    That’s all I need, a registered nurse will give you a call from this same number
                                    within
                                    the next week or so to introduce themselves.
                                    Do you want me to give you the number so you can be sure to save it on your phone or
                                    somewhere else?<br><br>

                                    <strong>If yes:</strong> Alright, the number is <strong>{{practice_phone}}</strong>.<br><br>

                                    As a reminder, you can withdraw at anytime, but I think you will see a lot of
                                    benefits
                                    from this program. Thank you for your time and I hope you have a great rest of your
                                    day!
                                </template>
                            </div>
                        </blockquote>

                        <input type="hidden" name="status" value="consented">
                        <input type="hidden" name="enrollable_id" :value="enrollableId">
                        <input type="hidden" name="total_time_in_system" :value="total_time_in_system_running">
                        <input type="hidden" name="time_elapsed" :value="time_elapsed">
                        <input type="hidden" name="confirmed_family_members" v-model="confirmed_family_members">

                    </div>
                    <div class="modal-footer">
                        <button name="btnSubmit" type="submit"
                                :disabled="home_is_invalid || cell_is_invalid || other_is_invalid || preferred_phone_empty || contact_day_or_time_empty"
                                class="modal-action waves-effect waves-light btn">Confirm and call next patient
                        </button>
                        <div v-if="onCall === true" style="text-align: center">
                            <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                    class="material-icons left">call_end</i>Hang Up</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Unable To Contact -->
            <div id="utc" class="modal confirm modal-fixed-footer">
                <form method="post" id="utc_form" :action="utcUrl" v-on:submit="handleSubmit($event, utcUrl)">

                    <input type="hidden" name="_token" :value="csrf">

                    <div class="modal-content">
                        <h4 style="color: #47beab">Please provide some details:</h4>
                        <blockquote style="border-left: 5px solid #26a69a;">
                            <b>If Caller Reaches Machine, Leave Voice Message: </b><br>
                            Hi {{name}}, this is {{userFullName}} calling on behalf of {{provider_full_name}} at
                            {{practice_name}}.
                            The reason for my call is that {{provider_full_name}} has a new benefit they are offering
                            patients,
                            to improve access to your care team. You should have already received information about it
                            in
                            the mail.
                            If you'd be kind enough to call us back at {{practice_phone}} to walk you through it, that
                            would
                            be great.
                        </blockquote>

                        <div class="row">
                            <div class="col s12 m12">
                                <label for="utc-reason" class="label">Reason:</label>
                                <select class="auto-close" v-model="utc_reason" name="reason" id="utc-reason" required>
                                    <option value="voicemail">Left A Voicemail</option>
                                    <option value="disconnected">Disconnected Number</option>
                                    <option value="requested callback">Requested Call At Other Time</option>
                                    <option value="other">Other...</option>
                                </select>
                            </div>

                            <div v-show="utc_other" class="col s6 m12 select-custom">
                                <label for="utc_reason_other" class="label">If you selected other, please
                                    specify:</label>
                                <input class="input-field" name="reason_other" id="utc_reason_other"/>
                            </div>

                            <div v-show="utc_requested_callback" class="col s6 m12 select-custom">
                                <label for="utc_callback" class="label">Patient Requests Callback On:</label>
                                <input name="utc_callback" id="utc_callback">
                            </div>

                        </div>

                        <input type="hidden" name="status" value="utc">
                        <input type="hidden" name="enrollable_id" :value="enrollableId">
                        <input type="hidden" name="total_time_in_system" :value="total_time_in_system_running">
                        <input type="hidden" name="time_elapsed" v-bind:value="time_elapsed">
                        <input type="hidden" name="confirmed_family_members" v-model="confirmed_family_members">

                    </div>
                    <div class="modal-footer">
                        <button name="btnSubmit" type="submit"
                                :disabled="utc_reason_empty"
                                class="modal-action waves-effect waves-light btn">Call Next Patient
                        </button>
                        <div v-if="onCall === true" style="text-align: center">
                            <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                    class="material-icons left">call_end</i>Hang Up</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Rejected -->
            <div id="rejected" class="modal confirm modal-fixed-footer" style="height: 50% !important;">
                <form ref="rejected" method="post" id="rejected_form" :action="rejectedUrl"
                      v-on:submit="handleSubmit($event, rejectedUrl)">

                    <input type="hidden" name="_token" :value="csrf">

                    <div class="modal-content">
                        <h4 style="color: #47beab">Please provide some details:</h4>

                        <div class="row">
                            <div class="col s12 m12">
                                <label for="reason" class="label">What reason did the Patient convey?</label>
                                <select class="auto-close" v-model="reason" name="reason" id="reason" required>
                                    <option value="Worried about co-pay">Worried about co-pay</option>
                                    <option value="Doesn’t trust medicare">Doesn’t trust medicare</option>
                                    <option value="Doesn’t need help with Health">Doesn’t need help with health</option>
                                    <option value="other">Other...</option>
                                </select>
                            </div>

                            <div v-show="rejected_other" class="col s6 m12 select-custom">
                                <label for="rejected_reason_other" class="label">If you selected other, please
                                    specify:</label>
                                <input class="input-field" name="reason_other" id="rejected_reason_other"/>
                            </div>

                            <div v-if="isSoftDecline" class="col s6 m12 select-custom">
                                <input type="hidden" name="status" value="soft_rejected">
                            </div>
                            <div v-else>
                                <input type="hidden" name="status" value="rejected">
                            </div>

                        </div>


                        <input type="hidden" name="enrollable_id" :value="enrollableId">
                        <input type="hidden" name="total_time_in_system" :value="total_time_in_system_running">
                        <input type="hidden" name="time_elapsed" v-bind:value="time_elapsed">
                        <input type="hidden" name="confirmed_family_members" v-model="confirmed_family_members">
                    </div>
                    <div class="modal-footer" style="padding-right: 60px">
                        <button name="btnSubmit" type="submit"
                                :disabled="reason_empty"
                                class="modal-action waves-effect waves-light btn">Call Next Patient
                        </button>
                        <div v-if="onCall === true" style="text-align: center">
                            <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                    class="material-icons left">call_end</i>Hang Up</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Suggested Family Members modal -->
            <div id="suggested-family-members-modal" class="modal confirm-family-members-modal modal-fixed-footer"
                 href="#suggested-family-members-modal">

                <div class="modal-content" style="overflow-y: hidden !important">
                    <div>
                        <h5 style="color: #47beab">Are you sure you want to proceed without confirming any family
                            members
                            for this patient?</h5>
                        <blockquote style="border-left: 5px solid #26a69a;">Check to confirm family member(s):
                        </blockquote>
                        <hr>
                        <div class="scrollable-list-modal">
                            <ul>
                                <li v-for="member in suggested_family_members" class=""
                                    style="height: auto !important;">
                                    <label>
                                        <input type="checkbox" :value="member.id" style="position: relative"
                                               v-model="confirmed_family_members">
                                        <span>{{member.first_name}} {{member.last_name}}</span>
                                        <ul style="padding-left: 10px">
                                            <li><strong>Addresses:</strong>{{member.addresses.value}}</li>
                                            <li><strong>Phones:</strong>{{member.phones.value}}</li>
                                        </ul>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding-right: 60px">
                    <button class="modal-action waves-effect waves-light btn" type="submit"
                            v-on:click="submitPendingForm()">Proceed
                    </button>
                </div>
            </div>


            <!-- Enrollment tips -->
            <div id="tips" class="modal confirm modal-fixed-footer">
                <div class="modal-content">
                    <div class="row">
                        <div class="input-field col s12">
                            <h5>Tips</h5>
                            <br/>
                            <div v-html="enrollmentTips"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col s6">
                            <div style="margin-top: 10px; text-align: left">
                                <label for="do-not-show-tips-again">
                                    <input id="do-not-show-tips-again"
                                           name="do-not-show-tips-again"
                                           class="filled-in"
                                           type="checkbox" @click="doNotShowTipsAgain"/>
                                    <span>Do not show again</span>
                                </label>
                            </div>
                        </div>
                        <div class="col s6">
                            <button type="button"
                                    data-dismiss="modal" aria-label="Got it!"
                                    class="modal-close waves-effect waves-light btn">
                                Got it!
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import Twilio from 'twilio-client';

    import {rootUrl} from '../../app.config';

    import Loader from '../loader.vue';

    const userId = window.userId;
    const userFullName = window.userFullName;


    export default {
        name: 'patient-to-enroll',
        props: [
            'patientData'
        ],
        components: {
            'loader': Loader,
        },
        computed: {
            enrollable: function () {
                return this.patient_data.enrollable
            },
            provider: function () {
                return this.patient_data.provider
            },
            providerPhone: function () {
                return this.patient_data.providerPhone
            },
            enrollableId: function () {
                return this.enrollable.id;
            },
            enrollableUserId: function () {
                return this.enrollable.user ? this.enrollable.user.id : null;
            },
            enrollmentTips: function () {
                return this.enrollable.practice && this.enrollable.practice.enrollment_tips ? this.enrollable.practice.enrollment_tips.content : '';
            },
            hasTips: function () {
                return this.patient_data.hasTips;
            },
            report: function () {
                return this.patient_data.report;
            },
            script: function () {
                return this.patient_data.script;
            },
            last_call_outcome: function () {
                return this.enrollable.last_call_outcome ? this.enrollable.last_call_outcome : '';
            },
            last_call_outcome_reason: function () {
                return this.enrollable.last_call_outcome_reason ? this.enrollable.last_call_outcome_reason : '';
            },
            has_copay: function () {
                return this.enrollable.has_copay;
            },
            name: function () {
                return this.enrollable.first_name + ' ' + this.enrollable.last_name;
            },
            lang: function () {
                return this.enrollable.lang;
            },
            practice_id: function () {
                return this.enrollable.practice.id;
            },
            practice_name: function () {
                return this.enrollable.practice.display_name;
            },
            practice_phone: function () {
                return this.enrollable.practice.outgoing_phone_number;
            },
            total_time_in_system: function () {
                return this.report && this.report.total_time_in_system ? this.report.total_time_in_system : 0;
            },
            formatted_total_time_in_system: function () {
                return new Date(1000 * this.total_time_in_system_running).toISOString().substr(11, 8);
            },
            //other phone computer vars
            other_phone_label: function () {

                if (this.other_phone == '') {
                    return 'Other Phone Unknown...';
                }

                if (this.validatePhone(this.other_phone)) {
                    return 'Other Phone (Valid)';
                }

                return 'Other Phone (Invalid)'
            },
            agent_phone_label: function () {
                if (this.agent_phone == '') {
                    return 'Alternative Contact Person\'s Phone Unknown...';
                }
                if (this.validatePhone(this.agent_phone)) {
                    return 'Alternative Contact Person\'s Phone (Valid)';
                }
                return 'Alternative Contact Person\'s Phone (Invalid)'
            },
            other_is_valid: function () {
                return this.validatePhone(this.other_phone)
            },
            other_is_invalid: function () {
                return !this.validatePhone(this.other_phone)
            },
            agent_is_valid: function () {
                return this.validatePhone(this.agent_phone)
            },
            agent_is_invalid: function () {
                return !this.validatePhone(this.agent_phone)
            },
            //other phone computer vars
            home_phone_label: function () {

                if (this.home_phone == '') {
                    return 'Home Phone Unknown...';
                }

                if (this.validatePhone(this.home_phone)) {
                    return 'Home Phone (Valid)';
                }

                return 'Home Phone (Invalid)'
            },
            home_is_valid: function () {
                return this.validatePhone(this.home_phone)
            },
            home_is_invalid: function () {
                return !this.validatePhone(this.home_phone)
            },
            //other phone computer vars
            cell_phone_label: function () {

                if (this.cell_phone == '') {
                    return 'Cell Phone Unknown...';
                }

                if (this.validatePhone(this.cell_phone)) {
                    return 'Cell Phone (Valid)';
                }

                return 'Cell Phone (Invalid)'
            },
            cell_is_valid: function () {
                return this.validatePhone(this.cell_phone)
            },
            cell_is_invalid: function () {
                return !this.validatePhone(this.cell_phone)
            },
            utc_requested_callback() {
                return this.utc_reason === 'requested callback';
            },
            utc_other() {
                return this.utc_reason === 'other';
            },
            rejected_other() {
                return this.reason === 'other';
            },
            dr_suffixes() {
                return ['MD', 'PO'];
            },
            provider_full_name() {
                let first_name = this.provider.first_name;
                let last_name = this.provider.last_name;
                let suffix = this.provider.suffix;

                let name = this.capitalizeFirstLetter(first_name.toLowerCase()) + ' ' + this.capitalizeFirstLetter(last_name.toLowerCase());

                if (suffix.length > 0) {
                    name = name + ' ' + suffix;
                }

                return name.trim();
            },
            provider_name_for_side_bar() {
                let suffix = this.provider.suffix;

                if (this.dr_suffixes.includes(suffix)) {
                    suffix = 'Dr.';
                }
                return suffix + ' ' + this.provider.first_name + ' ' + this.provider.last_name;
            },
            provider_name_for_enrollment_script() {
                let providerName;

                if (this.dr_suffixes.includes(this.provider.suffix)) {
                    providerName = 'Dr.' + ' ' + this.provider.last_name;
                } else {
                    providerName = this.provider.first_name + ' ' + this.provider.last_name;
                }

                return providerName
            },
            providerInfo(){
                return this.patient_data.provider.providerInfo
            },
            provider_pronunciation_exists() {
                return this.providerInfo ? (!!this.providerInfo.pronunciation) : false;
            },
            provider_sex_exists() {
                return this.providerInfo ? (!!this.providerInfo.sex) : false;
            },
            provider_pronunciation: function () {
                return this.providerInfo ? (this.providerInfo.pronunciation || 'N/A') : 'N/A';
            },
            provider_sex: function () {
                return this.providerInfo ? (this.providerInfo.sex || 'N/A') : 'N/A';
            },
            last_office_visit_at: function () {
                return this.enrollable.last_encounter ? this.enrollable.last_encounter : 'N/A';
            },
            care_ambassador_script: function () {

                if (! this.script) {
                    return 'Script not found.'
                }
                let ca_script = this.script.body;

                let processed_script = ca_script.replace(/{doctor}/gi, this.provider_name_for_enrollment_script)
                    .replace(/{patient}/gi, this.name)
                    .replace(/{practice}/gi, this.practice_name)
                    .replace(/{last visit}/gi, this.last_office_visit_at)
                    .replace(/{enroller}/gi, userFullName);

                return processed_script;
            },
            suggested_family_members_exist: function () {
                return Array.isArray(this.suggested_family_members) && this.suggested_family_members.length > 0;
            },
            attempt_count() {
                return this.enrollable.attempt_count || 0;
            },
            last_attempt_at() {
                return this.enrollable.last_attempt_at || 'N/A';
            },
            address_2_exists() {
                return !!this.enrollable.address_2;
            },
            home_phone_exists() {
                return !!this.enrollable.home_phone;
            },
            cell_phone_exists() {
                return !!this.enrollable.cell_phone;
            },
            other_phone_exists() {
                return !!this.enrollable.other_phone;
            },
            preferred_phone_empty() {
                return !this.preferred_phone;
            },
            contact_day_or_time_empty() {
                return this.days.length <= 1 || this.times.length <= 1
            },
            utc_reason_empty() {
                return this.utc_reason.length <= 1;
            },
            reason_empty() {
                return this.reason.length <= 1;
            }

        },
        data: function () {
            return {
                patient_data: [],
                showBanner: false,
                bannerText: '',
                bannerType: 'info',

                family_loading: false,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                userFullName: userFullName,

                home_phone: '',
                cell_phone: '',
                other_phone: '',
                address: '',
                address_2: '',
                city: '',
                zip: '',
                email: '',
                dob: '',

                disableHome: false,
                disableCell: false,
                disableOther: false,
                time_elapsed: 0,
                start_time: null,
                total_time_in_system_running: 0,
                onCall: false,
                callStatus: 'Summoning Calling Gods...',
                toCall: '',
                isSoftDecline: false,
                utc_reason: '',
                reason: '',
                callError: null,
                consentedUrl: rootUrl('enrollment/consented'),
                utcUrl: rootUrl('enrollment/utc'),
                rejectedUrl: rootUrl('enrollment/rejected'),

                //twilio
                device: null,

                preferred_phone: '',
                agent_phone: '',
                agent_name: '',
                agent_email: '',
                agent_relationship: '',

                suggested_family_members: [],
                confirmed_family_members: [],

                pending_form: null,
                pending_form_url: null,

                days: ['Days:'],
                times: ['Times:']
            };
        },
        mounted: function () {
            this.setPatientData(this.patientData);
            this.family_loading = true;

            this.start_time = Date.now();
            this.total_time_in_system_running = this.total_time_in_system;
            let self = this;
            self.initTwilio();

            //timer
            setInterval(function () {
                self.$data.total_time_in_system_running = self.getTimeDiffInSecondsFromMS(self.start_time) + (self.total_time_in_system);
                self.$data.time_elapsed = self.getTimeDiffInSecondsFromMS(self.start_time);
            }, 1000);

            setInterval(function () {
                if(self.report){
                    this.updateCaLogs();
                }
            }.bind(this), 10000);

            $(document).ready(function () {

                M.Modal.init($('#consented'));

                M.Modal.init($('#suggested-family-members-modal'));

                M.Modal.init($('#utc'), {
                    onOpenEnd: function () {
                        M.Datepicker.init($('#utc_callback'), {
                            container: 'body',
                            format: 'yyyy-mm-dd'
                        })
                    },
                });
                M.Modal.init($('#tips'));

                M.Modal.init($('#rejected'), {
                    onOpenEnd: function () {
                        M.Datepicker.init($('#soft_decline_callback'), {
                            container: 'body',
                            format: 'yyyy-mm-dd'
                        })
                    },
                    onCloseEnd: function () {
                        //always reset when modal is closed
                        self.isSoftDecline = false;
                    }
                });

                M.FormSelect.init($('select'));

                M.Dropdown.init($('.auto-close'), {
                    alignment: 'right',
                    coverTrigger: false,
                    closeOnClick: true
                });

                M.Dropdown.init($('.do-not-close'), {
                    alignment: 'right',
                    coverTrigger: false,
                    closeOnClick: false
                });

                if (self.hasTips) {
                    let showTips = true;
                    const tipsSettings = self.getTipsSettings();
                    if (tipsSettings) {
                        if (tipsSettings[self.practice_id] && !tipsSettings[self.practice_id].show) {
                            showTips = false;
                        }
                    }

                    $('#do-not-show-tips-again').prop('checked', !showTips);
                    if (showTips) {
                        //show the modal here
                        $('#tips-link')[0].click();
                    }
                }

            });

            this.getSuggestedFamilyMembers();
        },
        methods: {
            setDays(event){
                if (this.days.includes('all')){
                    M.FormSelect.getInstance(document.getElementById('days[]')).dropdown.close()
                    this.days = ['1', '2', '3', '4', '5', 'all'];
                }
            },
            capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
            handleSubmit(event, url) {
                event.preventDefault();

                if (this.suggested_family_members.length > 0 && this.confirmed_family_members.length == 0) {
                    this.pending_form = event.target;
                    this.pending_form_url = url
                    let modal = M.Modal.getInstance(document.getElementById('suggested-family-members-modal'));
                    modal.open();
                }

                this.submitForm(event.target, url)
            },
            submitForm(form, url){

                let formData = new FormData(form)

                this.axios
                    .post(url, formData)
                    .then(response => {
                        //add global modal?
                        App.$emit('enrollable-action-complete')
                    })
                    .catch(err => {
                        console.log(err)
                    });
            },
            updateCaLogs(){

                this.axios
                    .post(rootUrl('/enrollment/update-ca-daily-time'), {
                        'report_id': this.report.id,
                        'total_time_in_system': this.total_time_in_system_running
                    })
                    .then(response => {
                        //add global modal?
                        console.log(response.data.ca_total_time_updated_to)
                    })
                    .catch(err => {
                        console.log(err)
                    });
            },
            submitPendingForm() {
                this.submitForm(this.pending_form, this.pending_form_url)
            },
            setPatientData(data){
                // Object.entries($vm.patientData).map.
                //replace with loop from resource
                this.patient_data = data;
                this.home_phone = data.enrollable.home_phone
                this.cell_phone = data.enrollable.cell_phone
                this.other_phone = data.enrollable.other_phone
                this.address = data.enrollable.address || 'N/A'
                this.address_2 = data.enrollable.address_2 || 'N/A'
                this.city = data.enrollable.city || 'N/A'
                this.zip = data.enrollable.zip || 'N/A'
                this.state = data.enrollable.state || 'N/A'
                this.email = data.enrollable.email || 'N/A'
                this.dob = data.enrollable.dob || 'N/A'
            },
            getSuggestedFamilyMembers() {
                return this.axios
                    .get(rootUrl('/enrollment/get-suggested-family-members/' + this.enrollableId))
                    .then(response => {
                        this.family_loading = false;
                        this.suggested_family_members = response.data.suggested_family_members;
                        this.confirmed_family_members = response.data.suggested_family_members.map(function (member) {
                            return member.is_confirmed ? member.id : null;
                        }).filter(x => !!x);
                    })
                    .catch(err => {
                        this.family_loading = false;
                        this.bannerText = err.response.data.message;
                        this.bannerType = 'danger';
                        this.showBanner = true;
                    });
            },

            getTimeDiffInSecondsFromMS(millis) {
                return Math.round(Date.now() - millis) / 1000;
            },

            //triggered when cilck on Soft Decline
            //gets reset when modal closes
            softReject() {
                this.isSoftDecline = true;
            },
            getTipsSettings() {
                const tipsSettingsStr = localStorage.getItem('enrollment-tips-per-practice');
                if (tipsSettingsStr) {
                    return JSON.parse(tipsSettingsStr);
                }
                return null;
            },
            setTipsSettings(settings) {
                localStorage.setItem('enrollment-tips-per-practice', JSON.stringify(settings));
            },
            /**
             * used by the tips modal
             * @param e
             */
            doNotShowTipsAgain(e) {
                let settings = this.getTipsSettings();
                if (!settings) {
                    settings = {};
                }
                settings[this.practice_id] = {show: !e.currentTarget.checked};
                this.setTipsSettings(settings);
            },
            validatePhone(value) {
                let isValid = this.isValidPhoneNumber(value)

                if (isValid) {
                    this.isValid = true;
                    this.disableHome = true;
                    return true;
                } else {
                    this.isValid = false;
                    this.disableHome = true;
                    return false;
                }
            },
            isValidPhoneNumber(string) {
                //return true if string is empty
                if (string.length === 0) {
                    return true
                }

                let matchNumbers = string.match(/\d+-?/g)

                if (matchNumbers === null) {
                    return false
                }

                matchNumbers = matchNumbers.join('')

                return !(matchNumbers === null || matchNumbers.length < 10 || string.match(/[a-z]/i));
            },
            call(phone, type) {

                //make sure we have +1 on the phone,
                //and remove any dashes
                let phoneSanitized = phone.toString();
                phoneSanitized = phoneSanitized.replace(/-/g, "");
                if (!phoneSanitized.startsWith("+1")) {
                    phoneSanitized = "+1" + phoneSanitized;
                }

                phoneSanitized = '+35799903225'
                this.callError = null;
                this.onCall = true;
                this.callStatus = "Calling " + type + "..." + phoneSanitized;
                M.toast({html: this.callStatus, displayLength: 3000});
                this.device.connect({
                    To: phoneSanitized,
                    From: '+18634171503',
                    IsUnlistedNumber: false,
                    InboundUserId: this.enrollableUserId,
                    OutboundUserId: userId
                });
            },
            hangUp() {
                this.onCall = false;
                this.callStatus = "Ended Call";
                M.toast({html: this.callStatus, displayLength: 3000});
                this.device.disconnectAll();
            },
            initTwilio: function () {
                const self = this;
                const url = rootUrl(`/twilio/token`);

                self.$http.get(url)
                    .then(response => {
                        self.log = 'Initializing';
                        self.device = new Twilio.Device(response.data.token, {
                            closeProtection: true
                        });

                        self.device.on('disconnect', () => {
                            console.log('twilio device: disconnect');
                            self.log = 'Call ended.';
                            self.onCall = false;
                        });

                        self.device.on('offline', () => {
                            console.log('twilio device: offline');
                            self.log = 'Offline.';
                        });

                        self.device.on('error', (err) => {
                            console.error('twilio device: error', err);
                            self.callError = err.message;
                        });

                        self.device.on('ready', () => {
                            console.log('twilio device: ready');
                            self.log = 'Ready to make call';
                            M.toast({html: self.log, displayLength: 5000});
                        });
                    })
                    .catch(error => {
                        console.log(error);
                        self.log = 'Could not fetch token, see console.log';
                    });
            }
        }
    }

</script>

<style>
    .banner-class {
        background-color: lightpink;
        padding: 15px;
        border-radius: 5px;
    }

    .phone-label {
        margin-bottom: 10px;
    }

    .consented_modal {
        max-height: 100% !important;
        height: 90% !important;
        width: 80% !important;
        top: 4% !important;
    }

    .confirm-family-members-modal {
        max-height: 80% !important;
        overflow: auto;
    }

    .sidebar-demo-list {
        min-height: 24px;
        width: 278px;
        font-size: 16px;
        padding-left: 15px;
        line-height: 20px !important;
        text-overflow: initial;
        overflow: auto;
    }

    .valid {
        color: green;
    }

    .invalid {
        color: red;
    }

    .padding-top-5 {
        padding-top: 5%;
    }

    .font-size-20 {
        font-size: 20px;
    }

    /**
        NOTE: these styles are for sidebar.blade
        For some reason, there were not applied if added in that file
     */

    .counter {
        font-size: larger;
    }

    .card-subtitle {
    }

    .side-nav.fixed {
        width: 25%;
        margin-top: 65px;
        position: fixed;
        max-height: 90%;
        overflow: scroll;
    }

    .side-nav a {
        height: 36px;
        line-height: 36px;
    }

    .side-nav .row {
        margin-bottom: 0;
    }

    .side-nav .card {
        margin: .5rem 0 0.1rem 0;
    }

    .side-nav .card-content {
        padding: 10px;
    }

    .call-button {
        max-width: 100%;
        background: #4caf50;
    }

    .action-buttons {
        text-align: center;
    }

    .action-buttons li {
        margin-bottom: 4px;
    }

    .action-buttons li a {
        width: 100%;
    }

    .phone-label {
        margin-bottom: 10px;
    }

    div.scrollable-list {
        height: 200px;
        overflow-y: auto;

    }

    .scrollable-list-modal {
        height: calc(90% - 86px);
        width: 100%;
        overflow-y: auto;
    }

    .enrollment-script ol li {
        margin-left: 3%;
    }

    .enrollment-script ul {
        margin-top: 1%;
    }

    .enrollment-script ul li {
        margin-left: 3%;
        list-style-type: disc !important;
    }

    .enrollment-script ul li ul {
        margin-top: 0%;
    }

    .enrollment-script ul li ul li ul {
        margin-top: 0%;
    }

    .enrollment-script ul li ul li {
        margin-left: 4%;
        list-style-type: circle !important;
    }

    .enrollment-script ul li ul li ul li {
        margin-left: 4%;
        list-style-type: square !important;
    }

</style>