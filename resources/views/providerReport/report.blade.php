<head>

</head>
<body>
<div>
    <div>
        <h3>Patient</h3>
        <hr>
    </div>
    <div>
        Patient Name: {{$patient->display_name}} <br>
        Date of Birth: {{$patient->getDob()}} <br>
        Age: {{$patient->getAge()}} <br>
        Address: {{$patient->getAddress()}} <br>
        City, State, Zip: {{$patient->getCityStateZip()}} <br>
        Provider: {{$patient->getProviderName()}}
        <hr>
    </div>
    <div>
        <div>
            <h4>Reason for Visit</h4>
        </div>
        <div>
            {{$report->reason_for_visit}} Annual Wellness Visit.
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Demographic Data</h4>
        </div>
        <div>
            The patient is a {{$report->demographic_data['age']}} year old {{$report->demographic_data['race']}} who
            identifies as {{$report->demographic_date['gender']}}.
            In general, the patient has self-assessed their health as {{$report->demographic_data['health']}}.
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Allergy History</h4>
        </div>
        <div>
            The patient has reported allergies to the following:
            @if($report->allergy_history['allergies'])
                @foreach($report->allergy_history['allergies'] as $allergy)
                    {{$allergy}},
                @endforeach()
                .
            @else
                NKA.
            @endif
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Medical History</h4>
        </div>
        <div>
            The patient has indicated having the following conditions:
            @if($report->medical_history['conditions'] && $report->medical_history['other_conditions'])
                @foreach($report->medical_history['conditions'] as $condition)
                    {{$condition}},
                @endforeach()
                .
                The patient has also reported
                @foreach($report->medical_history['other_conditions'] as $otherCondition)
                    {{$otherCondition}},
                @endforeach
                .
            @else
                None.
            @endif
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Medication History</h4>
        </div>
        <div>
            The patient has indicated they use

            @if($report->medication_history['medications'])
                @foreach($report->medication_history['medications'] as $medication)
                    {{$medication['dose']}}, {{$medication['name']}}, {{$medication['frequency']}}
                @endforeach()
                .
            @else
                Nothing.
            @endif
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Family Medical History</h4>
        </div>
        <div>
            The patient reports family history as follows:

            @if($report->family_medical_history['family_conditions'])
                @foreach($report->family_medical_history['family_conditions'] as $condition)
                    {{$condition['name']}} in {{$condition['family_member']}},
                @endforeach()
                .
            @else
                Nothing.
            @endif
        </div>
        <hr>
    </div>
    <div>
        <div>
            <h4>Immunization History</h4>
        </div>
        <div>
            The patient's immunization history is as follows:
            The patient HAS received the
            @foreach($report->immunization_history as $key => $value)
                @if($value === 'Yes')
                    {{$key}},
                @endif
            @endforeach
            vaccinations.
            The patient has not, or is unsure if they have received the
            @foreach($report->immunization_history as $key => $value)
                @if($value === 'No' || $value === 'Unsure')
                    {{$key}},
                @endif
            @endforeach
            vaccinations.
        </div>
        <hr>
    </div>
</div>
</body>
