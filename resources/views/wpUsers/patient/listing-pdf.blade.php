<?php

?>

<link href="{{ asset('/css/app.css') }}" rel="stylesheet">
<div class="table-responsive  panel" style="overflow: visible !important;">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Provider</th>
                <th>Practice</th>
                <th>Location</th>
                <th>CCM Status</th>
                <th>Careplan Status</th>
                <th>Withdrawn Reason</th>
                <th>DOB</th>
                <th>MRN</th>
                <th>Phone</th>
                <th>Age</th>
                <th>Registered on</th>
                <th>CCM</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $patient)
            <tr>
                <td> <a href="{{ route('patient.summary', [ 'patientId' => $patient['key'] ]) }}">{{$patient['first_name']}} {{$patient['last_name']}}</a> </td>
                <td> {{$patient['provider']}} </td>
                <td> {{$patient['site']}} </td>
                <td> {{$patient['location']}} </td>
                <td> {{$patient['ccm_status']}} </td>
                <td>
                    <?php
                        $status = $patient['careplan_status'];
                        if ($status && \Illuminate\Support\Str::contains($status, '{')) {
                            $status = ((object) json_decode($status))->status;
                        }
                        echo $status;
                    ?>
                </td>
                <td> {{$patient['withdrawn_reason']}}
                <td> {{$patient['dob']}} </td>
                <td> {{$patient['mrn']}} </td>
                <td> {{$patient['phone']}} </td>
                <td> {{$patient['age']}} </td>
                <td> {{$patient['reg_date']}} </td>
                <td>
                    <?php
                        $seconds = (int) ($patient['ccm_seconds'] ?? '0');
                        $hours   = str_pad((string) floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
                        $minutes = str_pad((string) (floor($seconds / 60) % 60), 2, '0', STR_PAD_LEFT);
                        $seconds = $seconds % 60;
                        echo $hours.':'.$minutes.':'.$seconds;
                    ?>
                </td>
            </tr>
            @endforeach
    </tbody>
    </table>
</div>
