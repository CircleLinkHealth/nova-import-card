@extends('layouts.surveysMaster')
@section('content')

    @if (isset($isPdf) && $isPdf)

        <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">

        <!-- found in surveysMaster but for some reason dompdf has issues with it -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">
    @endif

    <link href="{{ asset('css/pppReport.css') }}" rel="stylesheet">

    <div class="container report">

        <!-- this magic line here is needed for the pdf generation -->
        <!-- it turns out that if the first character is <strong>, the rest of the document is <strong> -->
        <!-- so I added this before "Patient Info" which is <strong> -->
        <div>&nbsp;</div>
        <div class="report-title">
            <div><strong>Patient Info</strong></div>
            <hr/>
        </div>
        <div class="report-data">
            Patient Name: <strong><span style="color: #50b2e2">{{$patient->display_name}}</span></strong> <br>
            Date of Birth (records): <strong>{{$patient->patientInfo->dob()}}</strong><br>
            Age (self-reported): <strong>{{$patient->getAge()}}</strong> <br>
            Address: <strong>{{$patient->address}}</strong> <br>
            City, State, Zip: <strong>{{$patient->city}}, {{$patient->state}}, {{$patient->zip}}</strong> <br>
            Provider: <strong>{{$patient->getBillingProviderName()}}</strong>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Reason for Visit</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                {{$reportData['reason_for_visit']}} Annual Wellness Visit.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Demographic Data</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient reports that they are a {{$reportData['demographic_data']['age']}} year
                old
                @if($reportData['demographic_data']['ethnicity'] === 'No')
                    non
                @endif
                hispanic&nbsp;/&nbsp;latino
                {{lcfirst($reportData['demographic_data']['race'])}} {{$reportData['demographic_data']['gender']}}.
                In general, the patient has self-assessed their health as {{$reportData['demographic_data']['health']}}.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Allergy History</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if(! empty($reportData['allergy_history']))
                    The patient has reported allergies to the following:
                    @foreach($reportData['allergy_history'] as $allergy)
                        {{$allergy}}{{$loop->last ? '.' : ', '}}
                    @endforeach
                @else
                    NKA.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Medical History</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if(empty($reportData['medical_history']) && empty($reportData['medical_history_other']))
                    None.
                @else
                    The patient has indicated having the following conditions:
                    @foreach($reportData['medical_history'] as $condition)
                        @if(!empty($condition['type']))
                            {{$condition['name']}} ({{$condition['type']}}) {{$loop->last ? '.' : ', '}}
                        @else
                            {{$condition['name']}} {{$loop->last ? '.' : ', '}}
                        @endif
                    @endforeach
                    @if(! empty($reportData['medical_history_other']))
                        The patient has also reported
                        @foreach($reportData['medical_history_other'] as $otherCondition)
                            {{$otherCondition}} {{$loop->last ? '.' : ', '}}
                        @endforeach
                    @endif
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Medication History</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if(! empty($reportData['medication_history']))
                    The patient has indicated they use
                    @foreach($reportData['medication_history'] as $medication)
                        {{$medication['dose']}}
                        of {{$medication['drug']}} {{$medication['frequency']}} {{$loop->last ? '.' : ', '}}
                    @endforeach
                @else
                    The patient has indicated that they do not take any medications regularly.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Family Medical History</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient reports family history as follows:

                @if($reportData['family_medical_history'])
                    @foreach($reportData['family_medical_history'] as $condition)
                        {{$condition['name']}} in
                        patient's {{$condition['family']}} {{$loop->last ? '.' : ', '}}
                    @endforeach
                @else
                    Nothing.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Immunization History</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient's immunization history is as follows:
                <br>
                @if(!empty($reportData['immunizations_received']))
                    The patient HAS received the
                    @foreach($reportData['immunizations_received'] as $immunization)
                        {{$immunization}} {{$loop->last ? ' ' : ', '}}
                    @endforeach
                    vaccinations.
                @endif
                @if (!empty($reportData['immunizations_not_received']))
                    <br>
                    <br>
                    The patient has not, or is unsure if they have received the
                    @foreach($reportData['immunizations_not_received'] as $immunization)
                        {{$immunization}} {{$loop->last ? ' ' : ', '}}
                    @endforeach
                    vaccinations.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Screenings</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient's screening history is as follows:
                <br>
                @if(! empty($reportData['screenings']))
                    @foreach($reportData['screenings'] as $title => $text)
                        <strong>{{$title}}</strong>{{$text}}<br>
                    @endforeach
                @else
                    N/A
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Mental State & Potential for Depression</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient has a PHQ-2 score of {{$reportData['mental_state']['score']}}. This indicates a
                diagnosis of {{$reportData['mental_state']['diagnosis']}}.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Vitals</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                <strong>Blood pressure: </strong>
                {{$reportData['vitals']['blood_pressure']['first_metric']}}
                &nbsp;/&nbsp;
                {{$reportData['vitals']['blood_pressure']['second_metric']}} mmHg <br>
                <strong>Height: </strong> {{$reportData['vitals']['height']['feet']}}
                feet, {{$reportData['vitals']['height']['inches']}} inches <br>
                <strong>Weight: </strong> {{$reportData['vitals']['weight']}} lbs <br>
                <strong>BMI: </strong> {{$reportData['vitals']['bmi']}} kg/m2 <br>
                As the patient has a {{$reportData['vitals']['bmi_diagnosis']}} BMI of {{$reportData['vitals']['bmi']}},
                they are considered {{$reportData['vitals']['body_diagnosis']}}.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Diet</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient's diet consists of {{$reportData['diet']['fruits_vegetables']}} servings of fresh fruits and
                vegetables, {{$reportData['diet']['grain_fiber']}} servings of whole grain or high fiber foods,
                and {{$reportData['diet']['fried_fatty']}} servings of fried or high fat foods on average each day. Over
                the
                past week, they have consumed {{$reportData['diet']['sugary_beverages']}} non-diet sugar-sweetened
                beverages.
                In the past two weeks, they {{$reportData['diet']['have_changed_diet']}} experienced a change in the
                amount they eat.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Social Factors</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if($reportData['social_factors']['tobacco']['has_used'] === 'No') The patient has never smoked or used
                tobacco products. @else
                    The patient last smoked or used tobacco
                    products {{$reportData['social_factors']['tobacco']['last_smoked']}}. They
                    smoked {{$reportData['social_factors']['tobacco']['amount']}} packs/day, but
                    @if($reportData['social_factors']['tobacco']['interest_in_quitting'] === 'Yes' || $reportData['social_factors']['tobacco']['interest_in_quitting'] === 'Maybe')
                        are interested in quitting.
                    @elseif($reportData['social_factors']['tobacco']['interest_in_quitting'] === 'No') are not
                    interested in
                    quitting. @else have already quit. @endif
                @endif
                <br>
                @if($reportData['social_factors']['alcohol']['drinks'] === 'Yes') They drink alcohol. On average, they
                have {{$reportData['social_factors']['alcohol']['amount']}} drinks of alcoholic beverages per week.
                @else
                    They do not drink alcohol.
                @endif
                <br>
                @if($reportData['social_factors']['recreational_drugs']['has_used'] === 'Yes') They have used
                recreational
                drugs in the past year. They
                have used
                @foreach($reportData['social_factors']['recreational_drugs']['type_of_drug'] as $drug)
                    {{$drug['name']}} {{$drug['frequency']}} times in the past year{{$loop->last ? '.' : ', '}}
                @endforeach
                @else
                    They have not used recreational drugs in the past year.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Sexual Activity</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if(strtolower($reportData['sexual_activity']['active']) === 'yes')
                    The patient is sexually active. The
                    patient @if(strtolower($reportData['sexual_activity']['multiple_partners']) === 'yes')does @else
                        does not @endif have
                    multiple sexual partners.
                    The patient {{strtolower($reportData['sexual_activity']['safe_sex'])}} practices safe sex.
                @else
                    The patient is not sexually active.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Exercise & Activity Levels</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient exercises or reaches a moderate activity
                level {{$reportData['exercise_activity_levels']}}.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Functional Capacity and (ADL)</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if(empty($reportData['functional_capacity']['needs_help_for_tasks']))
                    The patient does not need any help performing any daily tasks.
                @else
                    The patient needs help to do the following tasks:
                    @foreach($reportData['functional_capacity']['needs_help_for_tasks'] as $task)
                        {{$task}}{{$loop->last ? '.' : ', '}}
                    @endforeach
                    They {{$reportData['functional_capacity']['have_assistance']}} have someone to assist them with
                    these tasks.
                @endif
                <br>
                <br>
                <strong>MCI&nbsp;/&nbsp;Cognitive</strong>
                <br>
                The patient was {{$reportData['functional_capacity']['mci_cognitive']['clock']}} to put in the hour
                markers and the time at ten minutes past eleven o'clock.
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
                <strong>Hearing&nbsp;/&nbsp;Auditory Function</strong>
                <br>
                The patient {{$reportData['functional_capacity']['hearing_difficulty']}} difficulty with their hearing.
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Current Providers of Medical Care</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                Patient indicates they have the following providers and suppliers of medical care:
                <br>
                @if(! empty($reportData['current_providers']))
                    @foreach($reportData['current_providers'] as $provider)
                        {{$provider['provider_name']}}, located at {{$provider['location']}} as their
                        {{strtoupper($provider['specialty'])}}. They can be reached at {{$provider['phone_number']}}.
                        <br>
                    @endforeach
                @else
                    None.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Advanced Care Planning</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                @if (strcasecmp($reportData['advanced_care_planning']['has_attorney'], 'yes') === 0)
                    The patient has a Medical Power of Attorney.
                @else
                    The patient does not have a Medical Power of Attorney.
                @endif
                @if(strcasecmp($reportData['advanced_care_planning']['living_will'], 'yes') === 0)
                    The patient has a living will&nbsp;/&nbsp;advance directive.
                    @if(strcasecmp($reportData['advanced_care_planning']['existing_copy'], 'yes') === 0)
                        A copy of the patient's advance directive is available on file at the moment.
                    @else
                        A copy of the patient's advance directive is not available on file at the moment.
                    @endif
                @else
                    The patient does not have a living will&nbsp;/&nbsp;advance directive.
                @endif
            </div>
        </div>

        <hr class="margins-20"/>

        <div class="avoid-page-break">
            <div class="recommendation-title avoid-page-break">
                <strong>Specific Patient Requests</strong>
            </div>

            <br/>

            <div class="recommendations-area">
                The patient has commented:
                <br>
                @if(empty($reportData['specific_patient_requests'])) No further comments.
                @else {{$reportData['specific_patient_requests']}}
                @endif
            </div>
        </div>

        <hr class="margins-20"/>
    </div>
@endsection
