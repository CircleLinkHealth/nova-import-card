<div id="demographics" class="panel">
    <h1 v-text="demographics.name | full_name"></h1>

    {{--@{{ $data | json }}--}}

    <p class="narrative">
        <span class="general">
            {{--<strong>@{{demographics.name|display_name}}</strong> is a {% if demographics.dob %}<strong>@{{demographics.dob|age}}</strong> year old{% endif %}--}}
            {{--<strong>{% if demographics.race %}@{{demographics.race}} {% endif %}{% if demographics.marital_status %}@{{demographics.marital_status|lower}} {% endif %}@{{demographics.gender|lower}}</strong>--}}
            <strong v-if="demographics.religion">
                who is @{{demographics.religion}}
            </strong>
            <strong v-if="demographics.language">
                and speaks @{{demographics.language | isolanguage}}
            </strong>
        {{--</span>--}}
        {{--<span class="allergies">--}}
            {{--@{{demographics.gender|gender_pronoun|title}} has <strong class="@{{allergies|max_severity}}">@{{allergies|max_severity}} allergies</strong>.--}}
        {{--</span>--}}
        {{--<span class="yearReview">--}}
            {{--In the past year, @{{demographics.gender|gender_pronoun}}--}}
            {{--<span id="yearReviewEncounters">--}}
                {{--{% if encounters|since_days(365)|strict_length == 0 %}--}}
                    {{--did not have medical encounters--}}
                {{--{% else %}--}}
                    {{--had <strong>medical encounters</strong>--}}
                {{--{% endif %}--}}
            {{--</span> and has <span id="yearReviewMedications">--}}
                {{--{% if medications|since_days(365)|strict_length == 0 %}--}}
                    {{--not had any medications prescribed.--}}
                {{--{% else %}--}}
                    {{--been <strong>prescribed medications</strong>.--}}
                {{--{% endif %}--}}
            {{--</span>--}}
        {{--</span>--}}
    {{--</p>--}}
    {{--<dl id="demographicsExtras">--}}
        {{--<li>--}}
            {{--<dt>Birthday</dt>--}}
            {{--<dd>@{{demographics.dob|date("F j, Y")}}</dd>--}}
        {{--</li>--}}
        {{--<li>--}}
            {{--<dt>Address</dt>--}}
            {{--{% if demographics.address.street|length == 2 %}--}}
            {{--{% for line in demographics.address.street %}--}}
            {{--<dd>@{{line}}</dd>--}}
            {{--{% endfor %}--}}
            {{--{% else %}--}}
            {{--<dd>@{{demographics.address.street}}</dd>--}}
            {{--{% endif %}--}}
            {{--<dd>@{{demographics.address.city}}, @{{demographics.address.state}} @{{demographics.address.zip}}</dd>--}}
        {{--</li>--}}
        {{--<li>--}}
            {{--<dt>Telephone</dt>--}}
            {{--{% for phone in demographics.phones %}--}}
            {{--{% if phone %}<dd class="phone-@{{phone.type}}">@{{phone.type|title}}: <a href="@{{phone.number}}">@{{phone.number|format_phone}}</a></dd>{% endif %}--}}
            {{--{% else %}--}}
            {{--<dd>No known number</dd>--}}
            {{--{% endfor %}--}}
        {{--</li>--}}
    {{--</dl>--}}

    {{--<h3>Patient Contacts</h3>--}}

    {{--<dl>--}}
        {{--{% if demographics.guardian and demographics.guardian.name.family %}<li>--}}
            {{--<dt>@{{demographics.guardian.relationship|fallback("Guardian")}}</dt>--}}
            {{--<dd>@{{demographics.guardian.name|full_name}}</dd>--}}
            {{--{% for number in demographics.guardian.phone %}--}}
            {{--{% if number %}<dd class="phone-@{{loop.key}}">@{{loop.key|slice(0,1)}}: <a href="@{{number}}">@{{number|format_phone}}</a></dd>{% endif %}--}}
            {{--{% else %}--}}
            {{--<dd>No known number</dd>--}}
            {{--{% endfor %}--}}
        {{--</li>{% endif %}--}}
        {{--{% for contact in demographics.patient_contacts %}<li>--}}
            {{--<dt>@{{contact.relationship|title|fallback(contact.role_details.displayName)}},--}}
                {{--@{{contact.relationship_code.displayName|title)}}</dt>--}}
            {{--<dd>@{{contact.name|full_name}}</dd>--}}
            {{--{% for phone in contact.phones %}--}}
            {{--{% if phone %}<dd class="phone-@{{phone.type}}">@{{phone.type|title}}: <a href="@{{phone.number}}">@{{phone.number|format_phone}}</a></dd>{% endif %}--}}
            {{--{% else %}--}}
            {{--<dd>No known number</dd>--}}
            {{--{% endfor %}--}}
        {{--</li>{% endfor %}--}}
    {{--</dl>--}}
</div>