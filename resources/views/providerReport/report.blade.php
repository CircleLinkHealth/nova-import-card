@extends('surveysMaster')
@section('content')
    <link href="{{asset('css/providerReport.css')}}" rel="stylesheet">
    <div class="container report">
        <div class="report-title">
            <h3>Patient Info</h3>
            <hr>
        </div>
        <div>
            Patient Name: <span style="color: #50b2e2">{{$patient->display_name}}</span> <br>
            Date of Birth: <strong>{{$patient->patientInfo->birth_date}} </strong><br>
            Age: <strong>{{$patient->getAge()}}</strong> <br>
            Address: <strong>{{$patient->address}}</strong> <br>
            City, State, Zip: <strong>{{$patient->city}}, {{$patient->state}}, {{$patient->zip}}</strong> <br>
            Provider: <strong>{{$patient->getBillingProviderName()}}</strong>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Reason for Visit</h4>
            </div>
            <div class="section-body">
                {{$reportData['reason_for_visit']}} Annual Wellness Visit.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Demographic Data</h4>
            </div>
            <div class="section-body">
                The patient is a {{$reportData['demographic_data']['age']}} year old {{$reportData['demographic_data']['race']}} who
                identifies as {{$reportData['demographic_data']['gender']}}.
                In general, the patient has self-assessed their health as {{$reportData['demographic_data']['health']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Allergy History</h4>
            </div>
            <div class="section-body">
                The patient has reported allergies to the following:
                @if(! empty($reportData['allergy_history']))
                    @foreach($reportData['allergy_history'] as $allergy)
                        {{$allergy}}{{$loop->last ? '.' : ', '}}
                    @endforeach()
                @else
                    NKA.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Medical History</h4>
            </div>
            <div class="section-body">
                @if(empty($reportData['medical_history']) && empty($reportData['medical_history_other']))
                    None.
                @else
                    The patient has indicated having the following conditions:
                    @foreach($reportData['medical_history'] as $condition)
                        {{$condition['name']}}{{$condition['type']}}{{$loop->last ? '.' : ', '}}
                    @endforeach()
                    @if(! empty($reportData['medical_history_other']))
                        The patient has also reported
                        @foreach($reportData['medical_history_other'] as $otherCondition)
                            {{$otherCondition}}{{$loop->last ? '.' : ', '}}
                        @endforeach
                    @endif
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Medication History</h4>
            </div>
            <div class="section-body">
                The patient has indicated they use
                @if(! empty($reportData['medication_history']))
                    @foreach($reportData['medication_history'] as $medication)
                        {{$medication['dose']}}
                        of {{$medication['drug']}} {{$medication['frequency']}}{{$loop->last ? '.' : ', '}}
                    @endforeach()
                @else
                    None.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Family Medical History</h4>
            </div>
            <div class="section-body">
                The patient reports family history as follows:

                @if($reportData['family_medical_history'])
                    @foreach($reportData['family_medical_history'] as $condition)
                        {{$condition['name']}} in
                        patient's {{$condition['family']}}{{$loop->last ? '.' : ', '}}
                    @endforeach()
                @else
                    Nothing.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Immunization History</h4>
            </div>
            <div class="section-body">
                The patient's immunization history is as follows:
                <br>
                The patient HAS received the
                @foreach($reportData['immunizations_received'] as $immunization)
                    {{$immunization}}{{$loop->last ? ' ' : ', '}}
                @endforeach
                vaccinations.
                <br>
                <br>
                The patient has not, or is unsure if they have received the
                @foreach($reportData['immunizations_not_received'] as $immunization)
                    {{$immunization}}{{$loop->last ? ' ' : ', '}}
                @endforeach
                vaccinations.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Screenings</h4>
            </div>
            <div class="section-body">
                The patient's screening history is as follows:
                <br>
                @if(! empty($reportData['screenings']))
                    @foreach($reportData['screenings'] as $screening)
                        {{$screening}}
                    @endforeach
                @else
                    N/A
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Mental State & Potential for Depression</h4>
            </div>
            <div class="section-body">
                The patient has a PHQ-2 score of {{$reportData['mental_state']['score']}}. This indicates a
                diagnosis of {{$reportData['mental_state']['diagnosis']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Vitals</h4>
            </div>
            <div class="section-body">
                <strong>Blood pressure: </strong> {{$reportData['vitals']['blood_pressure']['first_metric']}}
                /{{$reportData['vitals']['blood_pressure']['second_metric']}} mmHg <br>
                <strong>Height: </strong> {{$reportData['vitals']['height']['feet']}}
                feet, {{$reportData['vitals']['height']['inches']}} inches <br>
                <strong>Weight: </strong> {{$reportData['vitals']['weight']}} kg (lbs?) <br>
                <strong>BMI: </strong> {{$reportData['vitals']['bmi']}} kg/m2 <br>
                As the patient has a {{$reportData['vitals']['bmi_diagnosis']}} BMI of {{$reportData['vitals']['bmi']}},
                they are considered {{$reportData['vitals']['body_diagnosis']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Diet</h4>
            </div>
            <div class="section-body">
                The patient's diet consists of {{$reportData['diet']['fruits_vegetables']}} servings of fresh fruits and
                vegetables, {{$reportData['diet']['grain_fiber']}} servings of whole grain or high fiber foods,
                and {{$reportData['diet']['fried_fatty']}} servings of fried or high fat foods on average each day. Over the
                past week, they have consumed {{$reportData['diet']['sugary_beverages']}} non-diet sugar-sweetened beverages.
                In the past two weeks, they {{$reportData['diet']['have_changed_diet']}} experienced a change in the amount they eat.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Social Factors</h4>
            </div>
            <div class="section-body">
                @if($reportData['social_factors']['tobacco']['has_used'] === 'No') The patient has never smoked or used
                tobacco products. @else
                    The patient last smoked or used tobacco
                    products {{$reportData['social_factors']['tobacco']['last_smoked']}}. They
                    smoked {{$reportData['social_factors']['tobacco']['amount']}} packs/day, but
                    @if($reportData['social_factors']['tobacco']['interest_in_quitting'] === 'Yes' || $reportData['social_factors']['tobacco']['interest_in_quitting'] === 'Maybe')
                        are interested in quitting.
                    @elseif($reportData['social_factors']['tobacco']['interest_in_quitting'] === 'No') are not interested in
                    quitting. @else have already quit. @endif
                @endif
                <br>
                @if($reportData['social_factors']['alcohol']['drinks'] === 'Yes') They drink alcohol. On average, they
                have {{$reportData['social_factors']['alcohol']['amount']}} drinks of alcoholic beverages per week.
                @else
                    They do not drink alcohol.
                @endif
                <br>
                @if($reportData['social_factors']['recreational_drugs']['has_used'] === 'Yes') They have used recreational
                drugs in the past year. They
                have used
                @foreach($reportData['social_factors']['recreational_drugs']['type_of_drug'] as $drug)
                    {{$drug['name']}} {{$drug['frequency']}} times in the past year{{$loop->last ? '.' : ', '}}
                @endforeach
                @else
                    They have not used recreational drugs in the past year.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Sexual Activity</h4>
            </div>
            <div class="section-body">
                @if(strtolower($reportData['sexual_activity']['active']) === 'yes')
                    The patient is sexually active. The
                    patient @if(strtolower($reportData['sexual_activity']['multiple_partners']) === 'yes')does @else does not @endif have
                    multiple sexual partners.
                    The patient {{strtolower($reportData['sexual_activity']['safe_sex'])}} practices safe sex.
                @else
                    The patient is not sexually active.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Exercise & Activity Levels</h4>
            </div>
            <div class="section-body">
                The patient exercises or reaches a moderate activity
                level {{$reportData['exercise_activity_levels']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Functional Capacity and (ADL)</h4>
            </div>
            <div class="section-body">
                @if(empty($reportData['functional_capacity']['needs_help_for_tasks']))
                    The patient does not need any help performing any daily tasks.
                @else
                    The patient needs help to do the following tasks:
                    @foreach($reportData['functional_capacity']['needs_help_for_tasks'] as $task)
                        {{$task}}{{$loop->last ? '.' : ', '}}
                    @endforeach
                    They {{$reportData['functional_capacity']['have_assistance']}} have someone to assist them with these tasks.
                @endif
                <br>
                <br>
                <strong>MCI/Cognitive</strong>
                <br>
                The patient was {{$reportData['functional_capacity']['mci_cognitive']['clock']}} to put in the hour markers and the time at ten minutes past eleven o'clock.
                The patient was able to recall {{$reportData['functional_capacity']['mci_cognitive']['word_recall']}}/3
                objects. The patient has a score of {{$reportData['functional_capacity']['mci_cognitive']['total']}}.
                This indicates a diagnosis of {{$reportData['functional_capacity']['mci_cognitive']['diagnosis']}}.
                <br>
                <br>
                <strong>Fall Risk</strong>
                <br>
                The patient {{$reportData['functional_capacity']['has_fallen']}} fallen in
                the past 6 months.
                <br>
                <br>
                <strong>Hearing/Auditory Function</strong>
                <br>
                The patient {{$reportData['functional_capacity']['hearing_difficulty']}} difficulty with their hearing.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Current Providers of Medical Care</h4>
            </div>
            <div class="section-body">
                Patient indicates they have the following providers and suppliers of medical care:
                <br>
                @if(! empty($reportData['current_providers']))
                    @foreach($reportData['current_providers'] as $provider)
                        {{$provider['provider_name']}}, located at {{$provider['location']}} as their
                        {{$provider['specialty']}}. They can be reached at {{$provider['phone_number']}}.
                        <br>
                    @endforeach
                @else
                    None.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Advanced Care Planning</h4>
            </div>
            <div class="section-body">
                The patient {{$reportData['advanced_care_planning']['has_attorney']}} a Medical Power of Attorney.
                The patient @if($reportData['advanced_care_planning']['living_will'] === 'yes') has a living will/advance
                directive.
                A copy of the patient's advance
                directive {{$reportData['advanced_care_planning']['existing_copy'] === 'yes'}}
                available on file at the moment.
                @else does not have a living will/advance directive. @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Specific Patient Requests</h4>
            </div>
            <div class="section-body">
                The patient has commented:
                <br>
                @if(empty($reportData['specific_patient_requests'])) No further comments.
                @else {{$reportData['specific_patient_requests']}}
                @endif

            </div>
        </div>
    </div>
@endsection
