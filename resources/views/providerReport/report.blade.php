@extends('surveysMaster')
@section('content')
    <link href="{{asset('css/providerReport.css')}}" rel="stylesheet">
    <div class="container report">
        <div class="report-title">
            <h3>Patient Info</h3>
            <hr>
        </div>
        <div>
            Patient Name: {{$patient->display_name}} <br>
            Date of Birth: {{$patient->patientInfo->birth_date}} <br>
            Age: TEST <br>
            Address: TEST <br>
            City, State, Zip: TEST <br>
            Provider: TEST
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Reason for Visit</h4>
            </div>
            <div class="section-body">
                {{$report->reason_for_visit}} Annual Wellness Visit.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Demographic Data</h4>
            </div>
            <div class="section-body">
                The patient is a {{$report->demographic_data['age']}} year old {{ucwords(strtolower($report->demographic_data['race']))}} who
                identifies as {{strtolower($report->demographic_data['gender'])}}.
                In general, the patient has self-assessed their health as {{strtolower($report->demographic_data['health'])}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Allergy History</h4>
            </div>
            <div class="section-body">
                The patient has reported allergies to the following:
                @if($report->allergy_history['allergies'])
                    @foreach($report->allergy_history['allergies'] as $allergy)
                        @if($allergy['name'])
                            {{ucwords(strtolower($allergy['name']))}}{{$loop->last ? '.' : ', '}}
                        @endif
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
                The patient has indicated having the following conditions:
                @if($report->medical_history['conditions'] && $report->medical_history['other_conditions'])
                    @foreach($report->medical_history['conditions'] as $condition)
                        {{ucwords(strtolower($condition['name']))}}@if($condition['type'])({{ucwords(strtolower($condition['type']))}}
                        )@endif{{$loop->last ? '.' : ', '}}
                    @endforeach()
                    The patient has also reported
                    @foreach($report->medical_history['other_conditions'] as $otherCondition)
                        {{ucwords(strtolower($otherCondition['name']))}}{{$loop->last ? '.' : ', '}}
                    @endforeach
                @else
                    None.
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

                @if($report->medication_history['medications'])
                    @foreach($report->medication_history['medications'] as $medication)
                        {{$medication['dose']}}
                        of {{$medication['drug']}} {{$medication['frequency']}}{{$loop->last ? '.' : ', '}}
                    @endforeach()
                @else
                    Nothing.
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

                @if($report->family_medical_history['family_conditions'])
                    @foreach($report->family_medical_history['family_conditions'] as $condition)
                        {{ucwords(strtolower($condition['name']))}} in patient's {{strtolower($condition['family'])}}{{$loop->last ? '.' : ', '}}
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
                @foreach($report->immunization_history as $key => $value)
                    @if($value === 'Yes')
                        {{$key}}{{$loop->last ? ' ' : ', '}}
                    @endif

                @endforeach
                vaccinations.
                <br>
                <br>
                The patient has not, or is unsure if they have received the
                @foreach($report->immunization_history as $key => $value)
                    @if($value === 'No' || $value === 'Unsure')
                        {{$key}}{{$loop->last ? '' : ', '}}
                    @endif
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
                @foreach($report->screenings as $key => $value)
                    {{--@if(! $value === '10+ years ago/Never/Unsure')--}}
                    <strong>{{ucwords(strtolower($key))}}</strong>: Had {{$value}} <br>
                    {{--@endif--}}
                @endforeach
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Mental State & Potential for Depression</h4>
            </div>
            <div class="section-body">
                The patient has a PHQ-2 score of {{$report->mental_state['depression_score']}}. This indicates a
                diagnosis of
                @if($report->mental_state['depression_score'] <= 2)
                    no depression.
                @else
                    potential depression - further testing may be required.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Vitals</h4>
            </div>
            <div class="section-body">
                <strong>Blood pressure: </strong> {{$report->vitals['blood_pressure']['first_metric']}}
                /{{$report->vitals['blood_pressure']['second_metric']}} mmHg <br>
                <strong>Height: </strong> {{$report->vitals['height']['feet']}}
                feet, {{$report->vitals['height']['inches']}} inches <br>
                <strong>Weight: </strong> {{$report->vitals['weight']}} kg (lbs?) <br>
                <strong>BMI: </strong> {{$report->vitals['bmi']}} kg/m2 <br>
                As the patient has @if($report->vitals['bmi'] < 18.5) a low @elseif($report->vitals['bmi'] > 25) a
                high @else a normal @endif BMI of {{$report->vitals['bmi']}},
                they are considered @if($report->vitals['bmi'] >= 30)
                    overweight. @elseif($report->vitals['bmi'] > 25) obese. @elseif($report->vitals['bmi']< 18)
                    underweight. @else normal. @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Diet</h4>
            </div>
            <div class="section-body">
                The patient's diet consists of {{$report->diet['fruits_vegetables']}} servings of fresh fruits and
                vegetables, {{$report->diet['grain_fiber']}} servings of whole grain or high fiber foods,
                and {{$report->diet['fried_fatty']}} servings of fried or high fat foods on average each day. Over the
                past week, they have consumed {{$report->diet['sugary_beverages']}} non-diet sugar-sweetened beverages.
                In the past two weeks, they
                @if($report->diet['change_in_diet'] === 'Yes') have @elseif($report->diet['change_in_diet'] === 'No')
                    have not @else N/A @endif experienced a change in the amount they eat.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Social Factors</h4>
            </div>
            <div class="section-body">
                @if($report->social_factors['tobacco']['has_used'] === 'No') The patient has never smoked or used
                tobacco products. @else
                    The patient last smoked or used tobacco
                    products {{$report->social_factors['tobacco']['last_smoked']}}. They
                    smoked {{$report->social_factors['tobacco']['amount']}} packs/day, but
                    @if($report->social_factors['tobacco']['interest_in_quitting'] === 'Yes' || $report->social_factors['tobacco']['interest_in_quitting'] === 'Maybe')
                        are interested in quitting.
                    @elseif($report->social_factors['tobacco']['interest_in_quitting'] === 'No') are not interested in
                    quitting. @else have already quit. @endif
                @endif
                <br>
                @if($report->social_factors['alcohol']['drinks'] === 'Yes') They drink alcohol. On average, they
                have{{$report->social_factors['alcohol']['amount']}} drinks of alcoholic beverages per week.
                @else
                    They do not drink alcohol.
                @endif
                <br>
                @if($report->social_factors['recreational_drugs']['has_used'] === 'Yes') They have used recreational
                drugs in the past year. They
                have used
                @foreach($report->social_factors['recreational_drugs']['type_of_drug'] as $drug)
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
                @if($report->sexual_activity['active'] === 'Yes')
                    The patient is sexually active. The
                    patient @if($report->sexual_activity['multiple_partners'] === 'Yes')does @else does not @endif have
                    multiple sexual partners.
                    The patient {{strtolower($report->sexual_activity['safe_sex'])}} practices safe sex.
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
                level {{$report->exercise_activity_levels['frequency']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Exercise & Activity Levels</h4>
            </div>
            <div class="section-body">
                The patient exercises or reaches a moderate activity
                level {{$report->exercise_activity_levels['frequency']}}.
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Functional Capacity and (ADL)</h4>
            </div>
            <div class="section-body">
                @if(empty($report->functional_capacity['needs_help_for_tasks']))
                    The patient does not need any help performing any daily tasks.
                    @else
                    The patient needs help to do the following tasks:
                    @foreach($report->functional_capacity['needs_help_for_tasks'] as $task)
                        {{ucwords(strtolower($task['name']))}}{{$loop->last ? '.' : ', '}}
                    @endforeach
                    They @if($report->functional_capacity['have_assistance'] === 'Yes') do @else do not @endif
                    have someone to assist them with these tasks.
                    @endif
                <br>
                <br>
                <strong>MCI/Cognitive</strong>
                <br>
                The patient was @if($report->functional_capacity['mci_cognitive']['clock'] === 2) able @else unable @endif to put in the hour markers and the time at ten minutes past eleven o'clock.
                    The patient was able to recall {{$report->functional_capacity['mci_cognitive']['word_recall']}}/3 objects. The patient has a score of {{$report->functional_capacity['mci_cognitive']['total']}}.
                    This indicates a diagnosis of @if($report->functional_capacity['mci_cognitive']['total'] >3 ) no cognitive impairment. @elseif($report->functional_capacity['mci_cognitive']['total'] === 3) mild cognitive impairment.
                @else dementia. @endif
                    <br>
                <br>
                <strong>Fall Risk</strong>
                <br>
                The patient @if($report->functional_capacity['has_fallen'] === 'Yes')has @else has not @endif fallen in
                the past 6 months.
                <br>
                    <br>
                <strong>Hearing/Auditory Function</strong>
                <br>
                The patient @if($report->functional_capacity['hearing_difficulty'] === 'Yes')
                    has @elseif($report->functional_capacity['hearing_difficulty'] === 'Sometimes') sometimes has
                @else does not have
                @endif
                difficulty with their hearing.
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
                @if(! empty($report->current_providers['providers']))
                    @foreach($report->current_providers['providers'] as $provider)
                        {{$provider['provider_name']}}, located at {{$provider['location']}} as their
                        {{$provider['specialty']}}. They can be reached at {{$provider['phone_number']}}.
                        <br>
                    @endforeach
                @else
                    Unknown.
                @endif
            </div>
            <hr>
        </div>
        <div>
            <div class="section-title">
                <h4>Advanced Care Planning</h4>
            </div>
            <div class="section-body">
                The patient @if($report->advanced_care_planning['has_attorney'] === 'Yes') has @else does not
                have @endif a Medical Power of Attorney.
                The patient @if($report->advanced_care_planning['living_will'] === 'Yes') has a living will/advance
                directive.
                A copy of the patient's advance
                directive @if($report->advanced_care_planning['existing_copy'] === 'Yes') is @else is not @endif
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
                @if(empty($report->specific_patient_requests)) No further comments.
                @else {{$report->specific_patient_requests}}
                @endif

            </div>
        </div>

    </div>
@endsection
