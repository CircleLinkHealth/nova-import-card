<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" ng-app> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" ng-app> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" ng-app> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" ng-app> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>CircleLink Health - Care Plan Manager</title>
    <meta name="description" content="Patient health records in the Blue Button format.">
    <meta name="viewport" content="width=device-width">

    <!-- Injected styles -->
    <link rel="stylesheet" href="/css/ccd-template.css" type="text/css" media="screen, projection"/>

    <!-- Injected scripts -->
    <script src="/js/ccd/modernizr.js"></script>
    <script src="/js/ccd/jquery-1.9.0.js"></script>
    <script src="/js/ccd/swig.js"></script>
    <script src="/js/ccd/bluebutton.min.js"></script>
    <script src="/js/ccd/bbclear.js"></script>
</head>

    <body>

    <section id="clh-template" class="container">

        <nav id="primaryNav" class="navbar navbar-default navbar-fixed-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <div class="navbar-brand">
                        <img src="/img/cpm-logo.png" height="40" width="70">
                    </div>
                </div>

                <form id="oldViewer" action="<?= route('ccd.old.viewer'); ?>" class="navbar-form navbar-left" method="post" target="_blank">
                    <div class="form-group">
                        <input id="sendThis" type="text" name="xml" style="display: none;" value="<?= urlencode($ccd); ?>">
                    </div>
                    <input type="submit" class="btn btn-default" value="View in Raw CCD Viewer" >
                </form>
<!--                <h1>CCD Viewer</h1>-->
                <ul class="nav navbar-nav">
                    <a href="#demographics"><li>Profile</li></a>
                    <a href="#allergies"><li>Allergies</li></a>
                    <a href="#problems"><li>Problems</li></a>
                    <a href="#medications"><li>Medications</li></a>
                    <a href="#immunizations"><li>Immunizations</li></a>
                    <a href="#history"><li>History</li></a>
                    <a href="#labs"><li>Lab Results</li></a>
                </ul>
            </div>
        </nav>

        <div id="demographics" class="panel">
                <h1>{{demographics.name|full_name}}</h1>

                <p class="narrative">
                    <span class="general">
                        <strong>{{demographics.name|display_name}}</strong> is a {% if demographics.dob %}<strong>{{demographics.dob|age}}</strong> year old{% endif %}
                        <strong>{% if demographics.race %}{{demographics.race}} {% endif %}{% if demographics.marital_status %}{{demographics.marital_status|lower}} {% endif %}{{demographics.gender|lower}}</strong>
                        {% if demographics.religion or demographics.language %}who {% if demographics.religion %}is <strong>{{demographics.religion}}</strong>{% if demographics.language %} and {% endif %}{% endif %}{% if demographics.language %}speaks <strong>{{demographics.language|isolanguage|title}}</strong>{% endif %}{% endif %}.
                    </span>
                    <span class="allergies">
                        {{demographics.gender|gender_pronoun|title}} has <strong class="{{allergies|max_severity}}">{{allergies|max_severity}} allergies</strong>.
                    </span>
                    <span class="yearReview">
                        In the past year, {{demographics.gender|gender_pronoun}}
                        <span id="yearReviewEncounters">
                            {% if encounters|since_days(365)|strict_length == 0 %}
                                did not have medical encounters
                            {% else %}
                                had <strong>medical encounters</strong>
                            {% endif %}
                        </span> and has <span id="yearReviewMedications">
                            {% if medications|since_days(365)|strict_length == 0 %}
                                not had any medications prescribed.
                            {% else %}
                                been <strong>prescribed medications</strong>.
                            {% endif %}
                        </span>
                    </span>
                </p>
                <dl id="demographicsExtras">
                    <li>
                        <dt>Birthday</dt>
                        <dd>{{demographics.dob|date("F j, Y")}}</dd>
                    </li>
                    <li>
                        <dt>Address</dt>
                        {% if demographics.address.street|length == 2 %}
                            {% for line in demographics.address.street %}
                            <dd>{{line}}</dd>
                            {% endfor %}
                        {% else %}
                        <dd>{{demographics.address.street}}</dd>
                        {% endif %}
                        <dd>{{demographics.address.city}}, {{demographics.address.state}} {{demographics.address.zip}}</dd>
                    </li>
                    <li>
                        <dt>Telephone</dt>
                        {% for phone in demographics.phones %}
                            {% if phone %}<dd class="phone-{{phone.type}}">{{phone.type|title}}: <a href="{{phone.number}}">{{phone.number|format_phone}}</a></dd>{% endif %}
                        {% else %}
                            <dd>No known number</dd>
                        {% endfor %}
                    </li>
                </dl>

            <h3>Patient Contacts</h3>

            <dl>
                {% if demographics.guardian and demographics.guardian.name.family %}<li>
                    <dt>{{demographics.guardian.relationship|fallback("Guardian")}}</dt>
                    <dd>{{demographics.guardian.name|full_name}}</dd>
                    {% for number in demographics.guardian.phone %}
                    {% if number %}<dd class="phone-{{loop.key}}">{{loop.key|slice(0,1)}}: <a href="{{number}}">{{number|format_phone}}</a></dd>{% endif %}
                    {% else %}
                    <dd>No known number</dd>
                    {% endfor %}
                </li>{% endif %}
                {% for contact in demographics.patient_contacts %}<li>
                    <dt>{{contact.relationship|title|fallback(contact.role_details.displayName)}},
                    {{contact.relationship_code.displayName|title)}}</dt>
                    <dd>{{contact.name|full_name}}</dd>
                    {% for phone in contact.phones %}
                    {% if phone %}<dd class="phone-{{phone.type}}">{{phone.type|title}}: <a href="{{phone.number}}">{{phone.number|format_phone}}</a></dd>{% endif %}
                    {% else %}
                    <dd>No known number</dd>
                    {% endfor %}
                </li>{% endfor %}
            </dl>
            </div>

        <div id="provider" class="panel">
            <h1>Provider</h1>
            <dl>
                {% if document.legal_authenticator %}<li>
                    <dt>{{document.legal_authenticator.assigned_person|full_name|fallback('')}}</dt>
                    <dd>{{document.legal_authenticator.representedOrganization.name|fallback('')}}</dd>
                    <dd>IdType: IdValue</dd>
                    {% for id in document.legal_authenticator.ids %}
                    {% if id %}<dd class="id">{{id.assigningAuthorityName}}: <span>{{id.extension}}</span></dd>{% endif %}
                    {% endfor %}
                </li>{% endif %}
            </dl>
        </div>

            <div id="allergies" class="panel">
                <h1>Allergies</h1>
                {% for allergy in allergies %}
                <!-- Hide null names -->
                {% if allergy.allergen.name %}
                    {% if loop.first %}<ul>{% endif %}
                        <li class="allergy-{{allergy|max_severity}}">
                            <h2>{{allergy.allergen.name|title}}</h2>
                            {% if allergy.severity %}<p>{{allergy.severity}}</p>{% endif %}
                            {% if allergy.reaction.name %}<p>Causes {{allergy.reaction.name|lower}}</p>{% endif %}
                        </li>
                        {% if loop.last %}</ul>{% endif %}
                {% endif %}
                {% else %}
                    <p>No known allergies</p>
                {% endfor %}
            </div>

            <div id="problems" class="panel">
                <h1>Problems</h1>
                {% for problem in problems %}
                    {% if loop.first %}<ul class="listless">{% endif %}
                    {% if problem.name or problem.translation.name %}
                    <li class="{{problem|problem_status}}-status-container">
                        <p class="status-box">{{problem|problem_status|fallback('Unknown')|capitalize}}</p>
                        <h2>
                            {% if problem.name %}
                                {{problem.name|title}}
                            {% else if problem.translation.name %}
                                {{problem.translation.name|title}}
                            {% endif %}
                        </h2>
                        {% if problem.comment %}<p>{{problem.comment}}</p>{% endif %}
                        {% if allergy.reaction.name %}<p>Causes {{allergy.reaction.name|lower}}</p>{% endif %}

                        <dl class="footer">
                            {% if problem.code_system %}<li>
                                <dt>Code</dt>
                                {% if problem.code_system %}
                                <dd>{{problem.code_system|oid|upper}}
                                    {% endif %}
                                    {% if problem.code %}:
                                    {{problem.code}}
                                </dd></li>{% endif %}
                            {% else if problem.translation.code_system%}<li>
                                <dt>Code</dt>
                                {% if problem.translation.code_system %}
                                <dd>{{problem.translation.code_system|oid|upper}}
                                    {% endif %}
                                    {% if problem.translation.code %}:
                                    {{problem.translation.code}}
                                    {% endif %}</dd>
                            </li>{% endif %}


                            {% if problem.date_range.start or problem.date_range.end %}<li>
                                <dt>Date Range</dt>
                                <dd>
                                    {% if problem.date_range.start %}{{problem.date_range.start|date('M j, Y')}}{% endif %}
                                    {% if problem.date_range.end %}&ndash; {{problem.date_range.end|date('M j, Y')}}{% endif %}
                                </dd>
                            </li>{% endif %}
                        </dl>
                    </li>{% endif %}
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known Problems</p>
                {% endfor %}

          </div>
            <div id="medications" class="panel">
                <h1>Medication History</h1>
                {% for med in medications %}
                    {% if loop.first %}<ul>{% endif %}
                    {% if med.product.name %}
                    <li class="{{med|medication_status}}-status-container">
                        <p class="status-box">{{med|medication_status|fallback('Unknown')|capitalize}}</p>
                            <header>
                                <h2>{{med.product.name|title}}</h2>
                                {% if med.administration.name %}<small>{{med.administration.name|title}}</small>{% endif %}
                                {% if med.reason.name %}<small>for {{med.reason.name}}</small>{% endif %}
                            </header>

                            <dl class="footer">
                                {% if med.prescriber.organization or med.prescriber.person %}<li>
                                    <dt>Prescriber</dt>
                                    {% if med.prescriber.organization %}<dd>{{med.prescriber.organization}}</dd>{% endif %}
                                    {% if med.prescriber.person %}<dd>{{med.prescriber.person}}</dd>{% endif %}
                                </li>{% endif %}

                                {% if med.text %}<li>
                                    Instructions:<dd>{{med.text | medicNameFormat}}</dd>
                                </li>{% endif %}


                                {% if med.date_range.start or med.date_range.end %}<li>
                                    <dt>Date</dt>
                                    <dd>
                                        {% if med.date_range.start %}{{med.date_range.start|date('M j, Y')}}{% endif %}
                                        {% if med.date_range.end %}&ndash; {{med.date_range.end|date('M j, Y')}}{% endif %}
                                    </dd>
                                </li>{% endif %}
                            </dl>
                        </li>
                    {% endif %}
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known medications</p>
                {% endfor %}
            </div>
            <div id="immunizations" class="panel">
                <h1>Immunizations</h1>
                {% for group in immunizations|group('product.name') %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>{{group.grouper}}</h2>
                        {% for item in group.list %}
                            {% if loop.first %}<ul class="pills">{% endif %}
                            <li>{{item.date|date('M j, Y')}}</li>
                            {% if loop.last %}</ul>{% endif %}
                        {% endfor %}
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known immunizations</p>
                {% endfor %}
            </div>
            <div id="history" class="panel">
                <h1>Medical History</h1>
                {% for encounter in encounters %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>{{encounter.date|date('M j, Y')}}</h2>
                        <dl>
                            <li>
                                <dt>Encounter</dt>
                                <dd class="head">{{encounter.name|fallback("Unknown Visit")|title}}</dd>
                                {% for finding in encounter.findings %}
                                    {% if finding.name %}<dd>Finding: {{finding.name|title}}</dd>{% endif %}
                                {% endfor %}
                            </li>
                            {% for problem in encounter|related_by_date('problems') %}
                                <li>
                                    <dt>Problem</dt>
                                    <dd class="head">{{problem.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for procedure in encounter|related_by_date('procedures') %}
                                <li>
                                    <dt>Procedure</dt>
                                    <dd class="head">{{procedure.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for medication in encounter|related_by_date('medications') %}
                                <li>
                                    <dt>Medication</dt>
                                    <dd class="head">{{medication.product.name}}</dd>
                                </li>
                            {% endfor %}
                            {% for immunization in encounter|related_by_date('immunizations') %}
                                <li>
                                    <dt>Immunization</dt>
                                    <dd class='head'>{{immunization.product.name}}</dd>
                                </li>
                            {% endfor %}
                        </dl>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known past encounters</p>
                {% endfor %}
            </div>
            <div id="labs" class="panel">
                <h1>Lab Results</h1>
                {% for panel in labResults %}
                    {% if loop.first %}<ul>{% endif %}
                    <li>
                        <h2>
                            <span class="date">{{panel.tests[0].date|date('M j, Y')}}</span>
                            {{panel.name|fallback("Laboratory Panel")}}
                        </h2>
                        <ul class="results">
                            <li class="header">
                                <span class="lab-component">Component</span>
                                <span class="lab-value">Value</span>
                                <span class="lab-low">Low</span>
                                <span class="lab-high">High</span>
                            </li>
                            {% for result in panel.tests %}
                                <li>
                                    <span class="lab-component">{{result.name}}</span>
                                    <span class="lab-value">{{result.value|fallback("Unknown")}}{% if result.unit %} {{result.unit|format_unit|raw}}{% endif %}</span>
                                    <span class="lab-low">{% if result.reference_range.low_value %}{{result.reference_range.low_value}} {{result.reference_range.low_unit|format_unit|raw}}{% endif %}</span>
                                    <span class="lab-high">{% if result.reference_range.high_value %}{{result.reference_range.high_value}} {{result.reference_range.high_unit|format_unit|raw}}{% endif %}</span>
                                </li>
                            {% endfor %}
                        </ul>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% endfor %}
            </div>
        </section>
        <div id="loader">
            <div id="warningGradientOuterBarG">
                <div id="warningGradientFrontBarG" class="warningGradientAnimationG">
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                    <div class="warningGradientBarLineG"></div>
                </div>
            </div>
            <p>Reticulating splines...</p>
        </div>
    </body>

</html>



<script style="display: none;" id="xmlBBData" type="text/plain"><?= $ccd; ?></script>

