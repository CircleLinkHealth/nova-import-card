<div class="container">
    <section class="patient-summary">
        <div class="patient-info__main">
            <div class="patient-info__main">
                <div class="row gutter">
                    <div class="col-xs-12">
                        <div class="row address-height-print">
                            <div class="col-xs-12 sender-address-print">
                                <div class="row">
                                    <div class="col-xs-12 address"><strong>En nombre del</strong></div>
                                    <div class="col-xs-7 address">
                                        <div>
                                            {{optional($patient->billingProviderUser())->getFullName()}}
                                        </div>
                                        <div>
                                            {{$patient->primaryPractice->display_name}}
                                        </div>
                                        <div>
                                            @if($patient->getPreferredLocationAddress())
                                                <div>{{$patient->getPreferredLocationAddress()->address_line_1}}</div>
                                                <div>{{$patient->getPreferredLocationAddress()->city}}
                                                    , {{$patient->getPreferredLocationAddress()->state}} {{$patient->getPreferredLocationAddress()->postal_code}}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xs-4 col-xs-offset-1 print-row text-right">
                                        <p>290 Harbor Drive</p>
                                        <p>Stamford, CT 06902</p>
                                        <div class="omr-bar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row receiver-address-padding">
                            <div class="col-xs-12 receiver-address-print">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="row">
                                            <div class="col-xs-12 address">{{strtoupper($patient->getFullName())}}</div>
                                            <div class="col-xs-12 address">{{strtoupper($patient->address)}}</div>
                                            <div class="col-xs-12 address"> {{strtoupper($patient->city)}}
                                                , {{strtoupper($patient->state)}} {{strtoupper($patient->zip)}}</div>
                                        </div>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <br>
                                        {{ Carbon\Carbon::now()->format('F d, Y') }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row gutter">
                    <div class="col-xs-10 welcome-copy">
                        <div class="row gutter">
                            Estimada {{$patient->getFullName()}},
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter row">
                            Dr. {{optional($patient->billingProviderUser())->getFullName()}}, ¡Bienvenido a la Gestión de
                            Atención
                            Personalizada!
                        </div>
                        <br>
                        <div class="row gutter">
                            Como quizás le mencionó el Dr. {{optional($patient->billingProviderUser())->getLastName()}} con
                            respecto a
                            este
                            programa por invitación, la atención personalizada es una parte importante para mantenerse
                            saludable.
                        </div>
                        <br>
                        <div class="row gutter">
                            Los beneficios incluyen:
                        </div>
                        <div class="row gutter"><br>
                            <ul type="disc row" style="list-style-type: disc;">
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Atención personalizada y apoyo por teléfono (enfermera registrada).
                                </li>
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Conexión con su proveedor a través de actualizaciones compartidas con el
                                    Dr. {{optional($patient->billingProviderUser())->getLastName()}}.
                                </li>
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Acceso a su equipo de atención médica desde la comodidad de su hogar, para ayudarle
                                    a evitarse
                                    las frecuentes visitas al consultorio y los copagos.
                                </li>
                            </ul>
                        </div>
                        <div class="row gutter">
                            Puesto que no hemos podido contactarnos con usted al {{$patient->getPrimaryPhone()}}, y este
                            programa
                            requiere que nuestros entrenadores de atención le llamen periódicamente, por favor puede
                            llamarnos
                            al {{$patient->primaryPractice->number_with_dashes}}, siempre que esté libre, para
                            registrarse
                            rápidamente y, si fuera necesario, ¿Nos puede proporcione un número de teléfono mejor?
                        </div>
                        <div class="row gutter">
                            Recuerde, si recibe una llamada de {{$patient->primaryPractice->number_with_dashes}}, es de
                            su equipo de
                            atención médica quien llama para registrarse. Por favor, guarde el número de teléfono en su
                            directorio
                            así sabrá que debe contestar la llamada.
                        </div>
                        <div class="row gutter text-bold text-center">
                        </div>
                        <div class="row gutter"><br><br>
                        </div>
                        <div class="row gutter">
                            Muchas gracias. ¡Estamos deseosos de que se beneficie de este valioso programa!
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                            <br>Mis mejores deseos,<br><br><br>
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                            Chelsea Pruett
                        </div>
                        <div class="row gutter">
                        </div>
                    </div>
                </div>
            </div>
            <div class="breakhere"></div>
        </div>
    </section>
</div>

