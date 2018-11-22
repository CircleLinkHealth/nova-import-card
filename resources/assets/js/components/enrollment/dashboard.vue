<template>
    <div id="enrollment_calls">

        <ul style="width:20%; margin-top:65px;" class="side-nav fixed">

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
                                <li class="sidebar-demo-list"><span id="name"><b>Name:</b>{{name}}</span></li>
                                <li class="sidebar-demo-list"><span id="name"><b>Language:</b> {{lang}}</span></li>
                                <li class="sidebar-demo-list"><span
                                        id="name"><b>Provider Name:</b>{{provider_name}}</span>
                                </li>
                                <li class="sidebar-demo-list"><span
                                        id="name"><b>Practice Name:</b>{{practice_name}}</span>
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
                            <div class="row">

                                <div v-if="callError">
                                    <blockquote>Call Status: {{ callError }}</blockquote>
                                </div>

                                <div v-if="onCall === true" style="text-align: center">

                                    <blockquote>Call Status: {{ this.callStatus }}</blockquote>
                                    <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
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
                            <ul>
                                <li>
                                    <a class="waves-effect waves-light btn" href="#consented">
                                        Consented
                                    </a>
                                </li>
                                <li>
                                    <a class="waves-effect waves-light btn" href="#utc" style="background: #ecb70e">
                                        No Answer
                                    </a>
                                </li>
                                <li>
                                    <a class="waves-effect waves-light btn" href="#rejected" style="background: red;">
                                        Hard Declined
                                    </a>
                                </li>
                                <li>
                                    <a class="waves-effect waves-light btn" href="#rejected"
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
        </ul>

        <div style="margin-left: 21%;">

            <div style="padding: 0px 10px; font-size: 16px;">

                <blockquote v-if="last_call_outcome !== ''">
                    Last Call Outcome: {{ last_call_outcome }}
                    <span v-if="last_call_outcome_reason !== ''">
                        <br/>
                        Last Call Comment: {{ last_call_outcome_reason }}
                    </span>
                </blockquote>

                <div class="enrollment-script">

                    <template v-if="has_copay">
                        <div v-if="lang === 'EN'">
                            <p><b>ENGLISH [speak clearly and cheerfully]</b>: Hi this is
                                {{userFullName}} calling on behalf of
                                <b>Dr. {{provider_name}}</b> at <b>{{practice_name}}</b>. I’m calling
                                for {{name}}. Is this {{name}}?</p>

                            <p>How are you doing today?</p>

                            <p>
                                The reason I’m calling is {{provider_name}} is starting to work with a new personalized
                                care program.

                                {{provider_name}} thinks it might be helpful for you and wants you to enroll.
                            </p>

                            <p>
                                And so what the program is: there would be a registered nurse (RN), who can call you
                                once or twice a month,
                                whichever you prefer to check on how you’re doing, or to see if you’re having any new
                                problems.
                                And then the nurse would report back to {{provider_name}} so that s/he would have that
                                information in his/her records.
                                This helps the doctor keep up a little better with how you’re doing in between visits
                                and keeps them updated on your medications
                                and vitals (such as blood pressures and blood sugars).
                            </p>

                            <p>
                                The program is offered through Medicare and it’s no cost if you have supplemental
                                insurance to cover a small ~$8 copay. So I was just calling
                                today to check if you would be interested in trying this?

                                <br><b>[patients then usually have questions...caller may have to reassure patient that
                                they will still see the

                                doctor regularly, RN calls are only a supplement to regular care - not a
                                replacement.]</b>
                            </p>

                            <p>
                                <b>[Note: if the patient is hesitant, then stress:]</b>
                                <br>“It is easy to cancel if you just want to try it for a month or two and see how it
                                goes.
                                All you would have to do is just let the nurse know that you don’t want to continue and
                                we will take your name back off of the list.”

                            </p>

                            <p>
                                <b>[if no, “not for me”]:</b>

                                <br>
                                That’s perfectly fine, there’s no pressure to sign up.
                                This is just a program to help the doctor keep up with you between visits.
                                <b>[if the patient then becomes curious]</b>
                                You’re welcome to give it a try and you can cancel at any time. Would you like to try it
                                out for a month and see how it goes?
                                This program is available long term, if you think you’d be interested at a later date -
                                would you like us to call back and check in with you then?”
                                <br>
                                <b>[also - provide patient with phone number to call back if they’d like to enroll
                                    later]
                                    [mark patient “soft decline” if the patient might want to enroll in the future]
                                    [mark patient “hard decline” if the patient isn’t interested now nor will they be
                                    interested in the future]
                                </b>
                            </p>

                            <p>
                                <b>[if patient says yes:]</b>
                                <br>
                                The only thing is you can only be a part of one doctor’s care management program at a
                                time. I just want
                                to check and make sure that you’re not already signed up for this. If not, then we can
                                go ahead and enroll you.
                                Did you prefer that the nurse call you once or twice a month? Are you currently on
                                dialysis? Are you receiving hospice services?

                                <br>[then collect the rest of the information]:

                                <br>[Enroller/Ambassador should fill out patient information in enrollment sheet /
                                Confirm patient’s best
                                contact #, preferred call times, e-mail and address. Also collect any specialist data
                                from patient]
                            </p>

                            <p>
                                <b>[at end of all info collected for “yes”]:</b>
                                <br>
                                “That’s all I need for now, The nurse will give you a call for the first time within the
                                next week and she will
                                introduce herself to you and give you the number where you can reach her anytime you
                                need to. They’ll be
                                calling from the same number I called you from today.”
                            </p>

                            <p>“Have a great day! Thanks!”</p>

                            <p>
                                <b>[If Caller Reaches Machine, Leave Voice Message:]</b>​ Hi this is
                                {{userFullName}} calling on behalf of <b>Dr. {{provider_name}}</b>
                                at <b>{{practice_name}}</b>. The doctor[s] have invited you to their new
                                personalized care management program.
                                Please give us a call at [number Ambassador calling from on page 2] to learn more.
                                Please note there is
                                nothing to worry about, this program just lets the Dr. take better care of you between
                                visits. Again the
                                number is [number Ambassador calling from]</p>
                        </div>
                        <div v-else>
                            <p><b>Speak clearly and cheerfully</b>: Hola, {{ name }} estoy llamando en nombre de
                                los doctores <b>Dr. {{provider_name}}</b> de la {{practice_name}}. Los médicos le han
                                invitado a su nuevo
                                programa de
                                gestión de atención personalizada y es posible que haya recibido una carta a este
                                efecto.</p>

                            <p><b>Dr. {{provider_name}}</b> piensan que este programa sería muy útil para usted y le
                                gustaría que se
                                inscribiera.
                                Permítame contarle algo sobre este programa y cómo puede ayudarle a mantenerse
                                saludable.</p>

                            <p>
                                <b>
                                    [patients interested usually discuss their conditions here.. be sure to listen​] [if
                                    patient asks where
                                    calling
                                    from, use the practice name in google sheet, e.g., CCN or Ferguson]
                                </b>
                            </p>

                            <p>Este es un nuevo programa de atención preventiva de Medicare para ayudarlo a usted y a su
                                médico a cuidar mejor
                                de su
                                salud. Medicare ha decidido que su enfoque de la medicina necesitaba mejoras. Antes,
                                Medicare era reactivo, a
                                menudo
                                esperaba hasta que los pacientes terminaban en el hospital para
                                proporcionarles atención médica, no era bueno para los pacientes y caro para Medicare.
                                Ahora, Medicare está
                                siendo
                                proactivo: proporcionando atención entre las visitas al médico para asegurarse que sus
                                afecciones están bajo
                                control, está tomando sus medicamentos y no tiene ningún síntoma que pueda estar
                                molestándolo.</p>

                            <p>Ahora, Medicare está siendo proactivo: proporcionando atención entre las visitas al
                                médico para
                                asegurarse que sus afecciones están bajo control, está tomando sus medicamentos y no
                                tiene ningún
                                síntoma que pueda estar molestándolo.</p>

                            <p>Es un programa gratuito si usted está en Medicaid o tiene un seguro suplementario.Si no,
                                hay un copago de
                                alrededor
                                de $ 8 por mes. Recuerde, este servicio puede ahorrarle visitas a Atención de Urgencia o
                                a la consulta del Dr.
                                conectándole a una enfermera. El valor entregado está muy por encima de los $ 8 por
                                mes.</p>

                            <p>He aquí algunos detalles sobre el programa:</p>

                            <p>
                            <li>Un encargado de cuidado personal, una enfermera registrada, le hará una rápida llamada
                                telefónica dos veces al
                                mes,
                                para brindarle apoyo, atención personalizada y para ver cómo está usted
                            </li>

                            <li> También puede dejarnos un mensaje las 24 horas los 7 días de la semana y uno de
                                nuestros encargados de atención
                                se
                                pondrá en contacto con usted en un tiempo razonable
                            </li>

                            <li> Puede retirarse del programa en cualquier momento que desee. Solo llámenos.</li>

                            <li> Solamente puedes ser parte de un programa de cuidados del Doctor a la vez</li>

                            <p>¿Puede informarle a su doctor que usted aceptó inscribirse en este programa? (Recuerde
                                que siempre
                                puede retirarse si no le gusta) </p>

                            <p>
                                <b>[Si el paciente acepta]</b>
                                ¡Estupendo!
                                1-¿Quiere quele llamemos directamente o hay alguien más con el cual quiere que nos
                                pongamos
                                en contacto?
                                2- [Confirme el mejor N° de contacto del paciente, los tiempos preferidos para llamarlo]
                                3-Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy llamando
                                [number of practice]. Por favor, guárdelo para que acepte la llamada cuando suene el
                                teléfono.
                                ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
                            </p>

                            <p><b>[Si el paciente no acepta, tenga en cuenta esto]:</b> Gracias por su tiempo y le
                                informaremos a su
                                médico</p>

                            <p><i>[Ambassador: Please click the appropriate button based on patient’s
                                answer and follow instructions in subsequent popup forms. Thank you!]</i></p>
                        </div>
                    </template>
                    <template v-else>
                        <div v-if="lang === 'EN'">
                            <p><b>ENGLISH [speak clearly and cheerfully]</b>: Hi this is
                                {{userFullName}} calling on behalf of
                                <b>Dr. {{provider_name}}</b> at <b>{{practice_name}}</b>. I’m calling
                                for {{name}}. is this {{name}}?</p>

                            <p>How are you doing today?</p>

                            <p>
                                The reason I’m calling is {{provider_name}} is starting to work with a new personalized
                                care program.

                                {{provider_name}} thinks it might be helpful for you and wants you to enroll.
                            </p>

                            <p>
                                And so what the program is: there would be a registered nurse (RN), who can call you
                                once or twice a month,
                                whichever you prefer to check on how you’re doing, or to see if you’re having any new
                                problems. And then the
                                nurse would report back to {{provider_name}} so that s/he would have that information
                                in his/her records.
                                This helps the doctor keep up a little better with how you’re doing in between visits
                                and keeps them updated on your
                                medications and vitals (such as blood pressures and blood sugars).
                            </p>

                            <p>
                                The program is offered through Medicare and it’s no cost if you have supplemental
                                insurance to cover a small ~$8 copay.
                                So I was just calling today to check if you would be interested in trying this?
                                <br><b>[patients then usually have questions...caller may have to reassure patient that
                                they will still see the
                                doctor regularly, RN calls are only a supplement to regular care - not a replacement.
                                ]</b>
                            </p>


                            <p>
                                <b>[Note: if the patient is hesitant, then stress:]</b>
                                <br>“It is easy to cancel if you just want to try it for a month or two and see how it
                                goes. All you would have to do is
                                just let the nurse know that you don’t want to continue and we will take your name back
                                off of the list.”
                            </p>

                            <p>
                                <b>[if no, “not for me”]:</b>


                                <br>
                                That’s perfectly fine, there’s no pressure to sign up.
                                This is just a program to help the doctor keep up with you between visits.
                                <b>[if the patient then becomes curious]</b>
                                You’re welcome to give it a try and you can cancel at any time. Would you like to try it
                                out for a month and see how it goes?
                                This program is available long term, if you think you’d be interested at a later date -
                                would you like us to call back and check in with you then?”
                                <br>
                                <b>[also - provide patient with phone number to call back if they’d like to enroll
                                    later]
                                    [mark patient “soft decline” if the patient might want to enroll in the future]
                                    [mark patient “hard decline” if the patient isn’t interested now nor will they be
                                    interested in the future]
                                </b>
                            </p>

                            <p>
                                <b>[if patient says yes:]</b>
                                <br>
                                The only thing is you can only be a part of one doctor’s care management program at a
                                time. I just want
                                to check and make sure that you’re not already signed up for this. If not, then we can
                                go ahead and enroll you.
                                Did you prefer that the nurse call you once or twice a month? Are you currently on
                                dialysis? Are you receiving hospice services?

                                <br>[then collect the rest of the information]:

                                <br>[Enroller/Ambassador should fill out patient information in enrollment sheet /
                                Confirm patient’s best
                                contact #, preferred call times, e-mail and address. Also collect any specialist data
                                from patient]
                            </p>

                            <p>
                                <b>[at end of all info collected for “yes”]:</b>
                                <br>
                                “That’s all I need for now, The nurse will give you a call for the first time within the
                                next week and she will
                                introduce herself to you and give you the number where you can reach her anytime you
                                need to. They’ll be
                                calling from the same number I called you from today.”
                            </p>

                            <p>“Have a great day! Thanks!”</p>


                            <p>
                                <b>[If Caller Reaches Machine, Leave Voice Message:]</b>​ Hi this is
                                {{userFullName}} calling on behalf of <b>Dr. {{provider_name}}</b>
                                at <b>{{practice_name}}</b>. The doctor[s] have invited you to their new
                                personalized care management program.
                                Please give us a call at [number Ambassador calling from on page 2] to learn more.
                                Please note there is
                                nothing to worry about, this program just lets the Dr. take better care of you between
                                visits. Again the number is [number Ambassador calling from]
                            </p>
                        </div>
                        <div v-else>
                            <p>
                                <b>Speak clearly and cheerfully</b>: Hola, {{ name }} estoy llamando en nombre de
                                los doctores Dr. {{provider_name}} de la {{practice_name}}. Cómo estás?
                            </p>

                            <p>
                                Soy bien. Los médicos le han invitado a su nuevo programa de gestión de atención
                                personalizada y es posible que
                                haya recibido una carta a este efecto.
                            </p>

                            <p>Dr. {{provider_name}} piensan que este programa sería muy útil para usted y le gustaría
                                que se inscribiera.
                                Permítame contarle algo sobre este programa y cómo puede ayudarle a mantenerse
                                saludable?</p>

                            <p>
                                <b>
                                    [patients interested usually discuss their conditions here.. be sure to listen​] [if
                                    patient asks where
                                    calling
                                    from, use the practice name in google sheet, e.g., CCN or Ferguson]
                                </b>
                            </p>

                            <p>Este es un nuevo programa de atención preventiva de Medicare para ayudarlo a usted y a su
                                médico a
                                cuidar mejor de su salud. Medicare ha decidido que su enfoque de la medicina necesitaba
                                mejoras.
                                Antes, Medicare era reactivo, a menudo esperaba hasta que los pacientes terminaban en el
                                hospital
                                para proporcionarles atención médica, no era bueno para los pacientes y caro para
                                Medicare.</p>

                            <p>Ahora, Medicare está siendo proactivo: proporcionando atención entre las visitas al
                                médico para
                                asegurarse que sus afecciones están bajo control, está tomando sus medicamentos y no
                                tiene ningún
                                síntoma que pueda estar molestándolo.</p>

                            <p>He aquí algunos detalles sobre el programa:</p>

                            <p>
                            <li>Un encargado de cuidado personal, una enfermera registrada, le hará una rápida llamada
                                telefónica dos veces al
                                mes,
                                para brindarle apoyo, atención personalizada y para ver cómo está usted
                            </li>

                            <li> También puede dejarnos un mensaje las 24 horas los 7 días de la semana y uno de
                                nuestros encargados de atención
                                se
                                pondrá en contacto con usted en un tiempo razonable
                            </li>

                            <li> Puede retirarse del programa en cualquier momento que desee. Solo llámenos.</li>

                            <li> Solamente puedes ser parte de un programa de cuidados del Doctor a la vez</li>

                            <p>¿Puede informarle a su doctor que usted aceptó inscribirse en este programa? (Recuerde
                                que siempre
                                puede retirarse si no le gusta) </p>

                            <p><b>[patients interested usually discuss their conditions here.. be sure to listen​] [if
                                patient asks where
                                calling
                                from, use the practice name in google sheet, e.g., CCN or Ferguson]</b></p>

                            <p><i>[Ambassador: Please click the appropriate button based on patient’s
                                answer and follow instructions in subsequent popup forms. Thank you!]</i></p>
                        </div>
                    </template>
                </div>
            </div>

            <div style="padding: 10px; margin-bottom: 15px"></div>
            <div style="text-align: center">

            </div>
        </div>

        <!-- MODALS -->

        <!-- Success / Patient Consented -->
        <div id="consented" class="modal confirm modal-fixed-footer consented_modal">
            <form method="post" id="consented_form" :action="consentedUrl">

                <div class="modal-content">
                    <h4 style="color: #47beab">Awesome! Please confirm patient details:</h4>
                    <blockquote style="border-left: 5px solid #26a69a;">
                        <span class="consented_title"><b>I.</b></span>

                        <b>Ask patient:</b>
                        <div class="enrollment-script">
                            <template v-if="lang === 'ES'">
                                ¿Quiere quele llamemos directamente o hay alguien más con el cual quiere quenos pongamos
                                en
                                contacto?
                            </template>
                            <template v-else>
                                Do you want us to call you directly or is there someone else we should contact?
                            </template>
                        </div>
                        <br>
                        <b>Use radio button to confirm patient's preferred phone number to receive care management
                            calls.</b>
                    </blockquote>
                    <div class="row">
                        <div class="col s6 m4 select-custom">
                            <input name="preferred_phone" type="radio" id="home_radio" value="home"
                                   :checked="home_phone != ''"/>
                            <label for="home_radio"
                                   :class="{valid: home_is_valid, invalid: home_is_invalid}">{{home_phone_label}}</label>
                            <input class="input-field" name="home_phone" id="home_phone" v-model="home_phone"/>
                        </div>
                        <div class="col s6 m4 select-custom">
                            <input name="preferred_phone" type="radio" id="cell_radio" value="cell"
                                   :checked="home_phone == '' && cell_phone != ''"/>
                            <label for="cell_radio"
                                   :class="{valid: cell_is_valid, invalid: cell_is_invalid}">{{cell_phone_label}}</label>
                            <input class="input-field" name="cell_phone" id="cell_phone" v-model="cell_phone"/>
                        </div>
                        <div class="col s6 m4 select-custom">
                            <input name="preferred_phone" type="radio" id="other_radio" value="other"
                                   :checked="home_phone == '' && cell_phone == '' && other_phone != ''"/>
                            <label for="other_radio"
                                   :class="{valid: other_is_valid, invalid: other_is_invalid}">{{other_phone_label}}</label>
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
                            <span class="consented_title"><b>III.</b></span> Please confirm the patient's preferred
                            contact days
                            and
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
                                   placeholder="Optional additional information"
                                   style="margin-bottom: 10px; padding-bottom: 18px;">
                        </div>
                    </div>

                    <blockquote style="border-left: 5px solid #26a69a;">
                        <span class="consented_title"><b>IV.</b></span>
                        <span style="color: red"><b>TELL PATIENT BEFORE HANGING UP!</b></span><br>
                        <div class="enrollment-script">
                            <template v-if="lang === 'ES'">
                                Una enfermera registrada le llamará en breve del mismo desde el cual lo estoy llamando
                                {{practice_phone}}. Por favor, guárdelo para que acepte la llamada cuando suene el
                                teléfono.
                                ¡Me alegro de haberme conectado! ¡Que tenga un muy buen día!
                            </template>
                            <template v-else>
                                A Registered Nurse will call you shortly from the same # I’m calling from,
                                {{practice_phone}}.
                                Please save it so you accept the call when she/he rings. So glad we
                                connected! Have a great day!
                            </template>
                        </div>
                    </blockquote>

                    <input type="hidden" name="status" value="consented">
                    <input type="hidden" name="enrollee_id" :value="enrolleeId">
                    <input type="hidden" name="total_time_in_system" :value="total_time_in_system">
                    <input type="hidden" name="time_elapsed" :value="time_elapsed">

                </div>
                <div class="modal-footer">
                    <button id="submit" name="submit" type="submit"
                            :disabled="home_is_invalid || cell_is_invalid || other_is_invalid"
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
            <form method="post" id="utc_form" :action="utcUrl"
                  class="">

                <div class="modal-content">
                    <h4 style="color: #47beab">Please provide some details:</h4>
                    <blockquote style="border-left: 5px solid #26a69a;">
                        <b>If Caller Reaches Machine, Leave Voice Message: </b><br>
                        Hi this is {{userFullName}} calling on
                        behalf of {{ provider_name }} at {{ practice_name }}. The doctor[s] have invited you to their
                        new
                        personalized care management program. Please give us a call at {{practice_phone}} to learn more.
                        Please note there is
                        nothing to worry about, this program just lets the Dr. take better care of you between visits.
                        Again the number is {{practice_phone}}
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
                    <input type="hidden" name="enrollee_id" :value="enrolleeId">
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

        <!-- Rejected -->
        <div id="rejected" class="modal confirm modal-fixed-footer">
            <form method="post" id="rejected_form" :action="rejectedUrl"
                  class="">

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


                    <input type="hidden" name="enrollee_id" :value="enrolleeId">
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

        <!-- Enrollment tips -->
        <div v-if="hasTips" id="tips" class="modal confirm modal-fixed-footer">
            <div class="modal-content">
                <div class="row">
                    <div class="input-field col s12">
                        <h5>Tips</h5>
                        <br/>
                        <template v-html="enrollmentTips"></template>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col s6">
                        <div style="margin-top: 10px">
                            <input id="do-not-show-tips-again"
                                   name="do-not-show-tips-again"
                                   type="checkbox" @click="doNotShowTipsAgain"/>
                            <label for="do-not-show-tips-again">Do not show again</label>
                        </div>
                    </div>
                    <div class="col s6 text-right">
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
</template>

<script>

    import {rootUrl} from '../../app.config';

    //Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    //for some reason i could not pass these as props from blade.php
    const hasTips = window.hasTips;
    const enrollee = window.enrollee;
    const userFullName = window.userFullName;
    const report = window.report;

    export default {
        name: 'enrollment-dashboard',
        props: [],
        components: {},
        computed: {
            enrolleeId: function () {
                return enrollee.id;
            },
            enrollmentTips: function () {
                return enrollee.practice && enrollee.practice.enrollment_tips ? enrollee.practice.enrollment_tips.content : '';
            },
            last_call_outcome: function () {
                return enrollee.last_call_outcome ? enrollee.last_call_outcome : '';
            },
            last_call_outcome_reason: function () {
                return enrollee.last_call_outcome_reason ? enrollee.last_call_outcome_reason : '';
            },
            has_copay: function () {
                return enrollee.has_copay;
            },
            name: function () {
                return enrollee.first_name + enrollee.last_name;
            },
            lang: function () {
                return enrollee.lang;
            },
            provider_name: function () {
                return enrollee.providerFullName;
            },
            practice_id: function () {
                return enrollee.practice.id;
            },
            practice_name: function () {
                return enrollee.practiceName;
            },
            practice_phone: function () {
                return enrollee.practice.outgoing_phone_number;
            },
            home_phone: function () {
                return enrollee.home_phone;
            },
            cell_phone: function () {
                return enrollee.cell_phone;
            },
            other_phone: function () {
                return enrollee.other_phone;
            },
            address: function () {
                return enrollee.address ? enrollee.address : 'N/A';
            },
            address_2: function () {
                return enrollee.address_2 ? enrollee.address_2 : 'N/A';
            },
            state: function () {
                return enrollee.state ? enrollee.state : 'N/A';
            },
            city: function () {
                return enrollee.city ? enrollee.city : 'N/A';
            },
            zip: function () {
                return enrollee.zip ? enrollee.zip : 'N/A';
            },
            email: function () {
                return enrollee.email ? enrollee.email : 'N/A';
            },
            dob: function () {
                return enrollee.dob ? enrollee.dob : 'N/A';
            },
            total_time_in_system: function () {
                return this.report.total_time_in_system;
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
                    return 'Other Phone Valid!';
                }

                return 'Other Phone Invalid..'
            },
            other_is_valid: function () {
                return this.validatePhone(this.other_phone)
            },
            other_is_invalid: function () {
                return !this.validatePhone(this.other_phone)
            },
            //other phone computer vars
            home_phone_label: function () {

                if (this.home_phone == '') {
                    return 'Home Phone Unknown...';
                }

                if (this.validatePhone(this.home_phone)) {
                    return 'Home Phone Valid!';
                }

                return 'Home Phone Invalid..'
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
                    return 'Cell Phone Valid!';
                }

                return 'Cell Phone Invalid..'
            },
            cell_is_valid: function () {
                return this.validatePhone(this.cell_phone)
            },
            cell_is_invalid: function () {
                return !this.validatePhone(this.cell_phone)
            }
        },
        data: function () {
            return {
                userFullName: userFullName,
                hasTips: hasTips,
                report: report,
                disableHome: false,
                disableCell: false,
                disableOther: false,
                time_elapsed: 0,
                total_time_in_system_running: 0,
                onCall: false,
                callStatus: 'Summoning Calling Gods...',
                toCall: '',
                isSoftDecline: false,
                callError: null,
                consentedUrl: rootUrl('enrollment/consented'),
                utcUrl: rootUrl('enrollment/utc'),
                rejectedUrl: rootUrl('enrollment/rejected'),
            };
        },
        mounted: function () {

            this.total_time_in_system_running = this.total_time_in_system;

            this.$http
                .post("/twilio/token", {
                    forPage: window.location.pathname,
                    practice: this.practice_id
                })
                .then(response => {

                        console.log(response.data);

                        this.callStatus = 'Caller Ready';
                        M.toast({html: this.callStatus, displayLength: 5000});
                        Twilio.Device.setup(response.data.token);
                        Twilio.Device.error((err) => {
                            this.callError = err.message;
                        });
                        Twilio.Device.disconnect(() => {
                            this.onCall = false;
                        });
                    }
                );

            let self = this;

            //timer
            setInterval(function () {
                self.$data.total_time_in_system_running++;
                self.$data.time_elapsed++;
            }, 1000);

            const consented = document.getElementById('consented');
            const utc = document.getElementById('utc');
            const tips = document.getElementById('tips');
            M.Modal.init([consented, utc, tips]);

            const rejected = document.getElementById('rejected');
            M.Modal.init([rejected], {
                complete: function () {
                    //always reset when modal is closed
                    self.isSoftDecline = false;
                }
            });

            const selects = document.querySelectorAll('select');
            M.FormSelect.init(selects);

            if (this.hasTips) {
                let showTips = true;
                const tipsSettings = this.getTipsSettings();
                if (tipsSettings) {
                    if (tipsSettings[this.practice_id] && !tipsSettings[this.practice_id].show) {
                        showTips = false;
                    }
                }

                $('#do-not-show-tips-again').prop('checked', !showTips);
                if (showTips) {
                    //show the modal here
                    $('#tips-link').click();
                }
            }
        },
        methods: {

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
                }
                else {
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
                this.callError = null;
                this.onCall = true;
                this.callStatus = "Calling " + type + "..." + phone;
                M.toast({html: this.callStatus, displayLength: 3000});
                Twilio.Device.connect({"phoneNumber": phone});
            },
            hangUp() {
                this.onCall = false;
                this.callStatus = "Ended Call";
                M.toast({html: this.callStatus, displayLength: 3000});
                Twilio.Device.disconnectAll();
            }
        }
    }

</script>
<style>

    .consented_modal {
        max-height: 100% !important;
        height: 90% !important;
        width: 80% !important;
        top: 4% !important;
    }

    .sidebar-demo-list {

        height: 24px;
        font-size: 16px;
        padding-left: 15px;
        line-height: 20px !important;

    }

    .valid {
        color: green;
    }

    .invalid {
        color: red;
    }

    .enrollment-script {
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

</style>