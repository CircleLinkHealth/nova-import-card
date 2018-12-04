<div>
    <?php

    $patient      = $assessment->patient()->first();
        $approver = $assessment->approver()->first();
    ?>
    <p>
        An 
        <a href="{{ route('patient.careplan.assessment.approver', [ 'patientId' => $patient['id'], 'approverId' => $approver['id'] ]) }}">assessment</a> 
        was done on {{Carbon::parse($assessment->updated_at)->format('m/d/Y')}} at 
        {{Carbon::parse($assessment->updated_at)->format('H:i:s')}} by {{ $approver['display_name'] }}:
    </p>
    @if ($notifiable && is_a($notifiable, Location::class))
    <table width="100%">
        <tr>
            <td width="50%">
                Patient Name
            </td>
            <td width="50%">
                {{ $patient['display_name'] }}
            </td>
        </tr>
        <tr>
            <td>
                Risk Level
            </td>
            <td>
                {{ $assessment->risk }}
            </td>
        </tr>
        <tr>
            <td>
                Risk Factors
            </td>
            <td>
                {{ join(', ', (array)json_decode($assessment->risk_factors)) }}
            </td>
        </tr>
        <tr>
            <td>
                Key Treatment Goals
            </td>
            <td>
                {{ $assessment->key_treatment }}
            </td>
        </tr>
        <tr>
            <td>
                Alcohol Misuse Counseling
            </td>
            <td>
                {{ $assessment->alcohol_misuse_counseling }}
            </td>
        </tr>
        <tr>
            <td>
                Diabetes Screening Risk
            </td>
            <td>
                {{ join(', ', (array)json_decode($assessment->diabetes_screening_risk)) }}
            </td>
        </tr>
        <tr>
            <td>
                Diabetes Screening Interval
            </td>
            <td>
                {{ $assessment->diabetes_screening_interval }}
            </td>
        </tr>
        <tr>
            <td>
                Diabetes Screening Last Date
            </td>
            <td>
                {{ $assessment->diabetes_screening_last_date }}
            </td>
        </tr>
        <tr>
            <td>
                Diabetes Screening Next Date
            </td>
            <td>
                {{ $assessment->diabetes_screening_next_date }}
            </td>
        </tr>
        <tr>
            <td>
                Eye Screening Last Date
            </td>
            <td>
                {{ $assessment->eye_screening_last_date }}
            </td>
        </tr>
        <tr>
            <td>
                Eye Screening Next Date
            </td>
            <td>
                    {{ $assessment->eye_screening_next_date }}
            </td>
        </tr>
        <tr>
            <td>
                Patient Functional Assistance Areas
            </td>
            <td>
                {{ join(', ', (array)json_decode($assessment->patient_functional_assistance_areas)) }}
            </td>
        </tr>
        <tr>
            <td>
                Patient Psychosocial Areas To Watch
            </td>
            <td>
                {{ join(', ', (array)json_decode($assessment->patient_psychosocial_areas_to_watch)) }}
            </td>
        </tr>
    </table>
    @endif
</div>