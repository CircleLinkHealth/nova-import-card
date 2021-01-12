<div class="container">
    <section class="patient-summary">
        <div class="patient-info__main">
            <div class="patient-info__main">
                <div class="row gutter">
                    <div class="col-xs-12">
                        <div class="row address-height-print">
                            <div class="col-xs-12 sender-address-print">
                                <div class="row">
                                    <div class="col-xs-12 address"><strong>On Behalf of</strong></div>
                                    <div class="col-xs-7 address">
                                        <div>
                                            {{optional($patient->billingProviderUser())->getFullName() ?? ''}}
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
                                        <div>290 Harbor Drive</div>
                                        <div>Stamford, CT 06902</div>
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
                            Dear {{$patient->getFullName()}},
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter row">
                            Welcome to Dr. {{optional($patient->billingProviderUser())->getFullName()}}'s Personalized Care
                            Management program!
                        </div>
                        <br>
                        <div class="row gutter">
                            As Dr. {{optional($patient->billingProviderUser())->getLastName()}} may have mentioned regarding
                            this
                            invite-only
                            program, personalized
                            care is an important part of staying healthy.
                        </div>
                        <br>
                        <div class="row gutter">
                            Benefits include:
                        </div>
                        <div class="row gutter"><br>
                            <ul type="disc row" style="list-style-type: disc;">
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Personalized care and support over the phone (registered nurse).
                                </li>
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Connection with your provider through updates shared with
                                    Dr. {{optional($patient->billingProviderUser())->getLastName()}}.
                                </li>
                                <li style="list-style-type: disc;margin: 15px 0;">
                                    Access to your care team from the comfort of your home, to help avoid frequent
                                    office visits and co-pays.
                                </li>
                            </ul>
                        </div>
                        <div class="row gutter">
                            Since we have been unable to reach you at {{$patient->getPrimaryPhone()}}, and this program
                            requires our
                            care
                            coaches to call periodically, can you please call us
                            at {{$patient->primaryPractice->number_with_dashes}}, whenever you are free,
                            to very quickly check in and, if necessary, give us a better phone number?
                        </div>
                        <div class="row gutter">
                            Remember, if you receive a call from {{$patient->primaryPractice->number_with_dashes}},
                            that is your
                            care team calling to check
                            in. Please save the phone number in your directory so you will know to answer the call.
                        </div>
                        <div class="row gutter text-bold text-center">
                        </div>
                        <div class="row gutter"><br><br>
                        </div>
                        <div class="row gutter">
                            Thanks so much. We are eager to have you benefit from this worthwhile program!
                        </div>
                        <div class="row gutter">
                        </div>
                        <div class="row gutter">
                            <br>Best,<br><br><br>
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
