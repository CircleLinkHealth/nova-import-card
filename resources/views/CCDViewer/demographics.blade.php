<div id="demographics" class="panel" xmlns="http://www.w3.org/1999/html">
    <h1 v-if="demographics.name">
        @{{ demographics.name | full_name }}
    </h1>

    <p class="narrative">
        <span class="general">
            <strong v-if="demographics.name">
                @{{ demographics.name | display_name | capitalize }}
            </strong>
            <span v-if="demographics.dob">
                is a <strong>@{{demographics.dob | age}}</strong> year old
            </span>
            <strong v-if="demographics.race">
                @{{demographics.race | lowercase}}
            </strong>
            <strong v-if="demographics.marital_status">
                @{{demographics.marital_status | lowercase}}
            </strong>
            <strong>
                @{{demographics.gender | lowercase}}
            </strong>
            <span v-if="demographics.religion">
                who is <strong>@{{demographics.religion}}</strong>
            </span>
            <span v-if="demographics.language">
                and speaks
                <strong>
                    @{{demographics.language | iso_language}}.
                </strong>
            </span>
        </span>
        <span class="allergies">
            <span v-if="demographics.gender">
                @{{demographics.gender | gender_pronoun | capitalize}} has
            </span>
            <strong v-if="allergies" class="@{{allergies | max_severity}}">
                @{{allergies | max_severity}} allergies</strong>.
        </span>

        <span v-if="demographics.gender" class="yearReview">
            In the past year, @{{demographics.gender | gender_pronoun}}
            <span v-if="encounters | since_days '365' | strict_length" id="yearReviewEncounters">
                <strong>had medical encounters</strong>
            </span>
            <span v-else>
                did not have medical encounters
            </span>
            and has
            <span v-if="medications | since_days '365' | strict_length" id="yearReviewMedications">
                been <strong>prescribed medications</strong>.
            </span>
            <span v-else>
                not had any medications prescribed.
            </span>
        </span>
    </p>
        {{--<dl id="demographicsExtras">--}}
        {{--<li>--}}
        {{--<dt>Birthday</dt>--}}
        {{--<dd>@{{demographics.dob | date("F j, Y")}}</dd>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<dt>Address</dt>--}}
        {{--{% if demographics.address.street | length == 2 %}--}}
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
        {{--{% if phone %}<dd class="phone-@{{phone.type}}">@{{phone.type | title}}: <a href="@{{phone.number}}">@{{phone.number | format_phone}}</a></dd>{% endif %}--}}
        {{--{% else %}--}}
        {{--<dd>No known number</dd>--}}
        {{--{% endfor %}--}}
        {{--</li>--}}
        {{--</dl>--}}

        {{--<h3>Patient Contacts</h3>--}}

        {{--<dl>--}}
        {{--{% if demographics.guardian and demographics.guardian.name.family %}<li>--}}
        {{--<dt>@{{demographics.guardian.relationship | fallback("Guardian")}}</dt>--}}
        {{--<dd>@{{demographics.guardian.name | full_name}}</dd>--}}
        {{--{% for number in demographics.guardian.phone %}--}}
        {{--{% if number %}<dd class="phone-@{{loop.key}}">@{{loop.key | slice(0,1)}}: <a href="@{{number}}">@{{number | format_phone}}</a></dd>{% endif %}--}}
        {{--{% else %}--}}
        {{--<dd>No known number</dd>--}}
        {{--{% endfor %}--}}
        {{--</li>{% endif %}--}}
        {{--{% for contact in demographics.patient_contacts %}<li>--}}
        {{--<dt>@{{contact.relationship | title | fallback(contact.role_details.displayName)}},--}}
        {{--@{{contact.relationship_code.displayName | title)}}</dt>--}}
        {{--<dd>@{{contact.name | full_name}}</dd>--}}
        {{--{% for phone in contact.phones %}--}}
        {{--{% if phone %}<dd class="phone-@{{phone.type}}">@{{phone.type | title}}: <a href="@{{phone.number}}">@{{phone.number | format_phone}}</a></dd>{% endif %}--}}
        {{--{% else %}--}}
        {{--<dd>No known number</dd>--}}
        {{--{% endfor %}--}}
        {{--</li>{% endfor %}--}}
        {{--</dl>--}}
</div>