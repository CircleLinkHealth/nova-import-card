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
    <link rel='stylesheet' href='/css/ccd-template.css' type='text/css' media='screen, projection'/>

    <!-- Injected scripts -->
    <?= View::make('partials.footer'); ?>
    <script src="/js/ccd/modernizr.js"></script>
    <script src="/js/ccd/jquery-1.9.0.js"></script>
    <script src="/js/ccd/swig.js"></script>
<!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/swig/1.4.1/swig.min.js"></script>-->
<!--    <script src="/js/ccd/bluebutton-0.0.10.js"></script>-->
    <script src="/js/ccd/bluebutton.js"></script>
    <script src="/js/ccd/bbclear.js"></script>
</head>

    <body>

    <section class="bb-template">
            <nav id="primaryNav">
                <div class="container">
                    <h1>CCD Viewer</h1>
                    <ul>
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
                        {% for number in demographics.phone %}
                            {% if number %}<dd class="phone-{{loop.key}}">{{loop.key|slice(0,1)}}: <a href="{{number}}">{{number|format_phone}}</a></dd>{% endif %}
                        {% else %}
                            <dd>No known number</dd>
                        {% endfor %}
                    </li>
                    {% if demographics.guardian and demographics.guardian.name.family %}<li>
                        <dt>{{demographics.guardian.relationship|fallback("Guardian")}}</dt>
                        <dd>{{demographics.guardian.name|full_name}}</dd>
                        {% for number in demographics.guardian.phone %}
                            {% if number %}<dd class="phone-{{loop.key}}">{{loop.key|slice(0,1)}}: <a href="{{number}}">{{number|format_phone}}</a></dd>{% endif %}
                        {% else %}
                            <dd>No known number</dd>
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
                            <h2>{{allergy.allergen.name}}</h2>
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
                    <li class="problem-{{problem|problem_status}}">
                        <p class="problem-status">{{problem.status}}</p>
                        <h2>
                            {% if problem.name %}
                                {{problem.name}}
                            {% else if problem.translation.name %}
                                {{problem.translation.name}}
                            {% endif %}
                        </h2>
                        {% if problem.comment %}<p>{{problem.comment}}</p>{% endif %}
                        {% if allergy.reaction.name %}<p>Causes {{allergy.reaction.name|lower}}</p>{% endif %}

                        <dl class="footer">
                            <!-- Get problem variables, if not empty -->
                            {% if problem.code or problem.code_system %}<li>
                                <dt>Code {% if problem.code_system_name %}<small>({{problem.code_system_name}})</small>{% endif %}</dt>
                                {% if problem.code_system %}<dd>{{problem.code_system}}</dd>{% endif %}
                                {% if problem.code %}<dd>{{problem.code}}</dd>{% endif %}
                            </li>
                            <!-- other wise, get problem translation variables -->
                            {% else if problem.translation.code or problem.translation.code_system %}<li>
                                <dt>Code {% if problem.translation.code_system_name %}<small>({{problem.translation.code_system_name}})</small>{% endif %}</dt>
                                {% if problem.translation.code_system %}<dd>{{problem.translation.code_system}}</dd>{% endif %}
                                {% if problem.translation.code %}<dd>{{problem.translation.code}}</dd>{% endif %}
                            </li>{% endif %}

                            {% if problem.date_range.start or problem.date_range.end %}<li>
                                <dt>Date Range</dt>
                                <dd>
                                    {% if problem.date_range.start %}{{problem.date_range.start|date('M j, Y')}}{% endif %}
                                    {% if problem.date_range.end %}&ndash; {{problem.date_range.end|date('M j, Y')}}{% endif %}
                                </dd>
                            </li>{% endif %}
                        </dl>
                    </li>
                    {% if loop.last %}</ul>{% endif %}
                {% else %}
                    <p>No known Problems</p>
                {% endfor %}

          </div>
            <div id="medications" class="panel">
                <h1>Medication History</h1>
                {% for med in medications %}
                    {% if loop.first %}<ul>{% endif %}
                    <li class="{{loop.cycle('odd', 'even')}}">
                        <header>
                            <h2>{{med.product.name}}</h2>
                            {% if med.administration.name %}<small>{{med.administration.name|title}}</small>{% endif %}
                            {% if med.reason.name %}<small>for {{med.reason.name}}</small>{% endif %}
                        </header>

                        <dl class="footer">
                            {% if med.prescriber.organization or med.prescriber.person %}<li>
                                <dt>Prescriber</dt>
                                {% if med.prescriber.organization %}<dd>{{med.prescriber.organization}}</dd>{% endif %}
                                {% if med.prescriber.person %}<dd>{{med.prescriber.person}}</dd>{% endif %}
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
                                    {% if finding.name %}<dd>Finding: {{finding.name}}</dd>{% endif %}
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