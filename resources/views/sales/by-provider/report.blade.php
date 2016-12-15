<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"
      xmlns="http://www.w3.org/1999/html">

<div class="page-header">
    <h1>{{$data['providerUser']->fullName}} ({{$data['providerUser']->primaryPracticeName}})
        <small><br />
            <b><span style="color: #50b2e2"> CircleLink Health </span>Sales Report - {{$data['range_start']}} to {{$data['range_end']}}</b></small>
    </h1>
</div>



<!--
'Overall Summary',
'Enrollment Summary',
'Financial Performance',
'Practice Demographics'
-->

@if(isset($data['sections']['Overall Summary']))
<div>
    <h3>Overall Summary</h3>

    <p>
        Last week at your offices CircleLink nurses placed {{$data['sections']['Overall Summary']['no_of_call_attempts']}}
        calls, including {{$data['sections']['Overall Summary']['no_of_successful_calls']}} successful phone sessions, totaling
        {{$data['sections']['Overall Summary']['total_ccm_time']}} care hours. We also collected
        {{$data['sections']['Overall Summary']['no_of_biometric_entries']}} vitals readings and our nurses forwarded
        {{$data['sections']['Overall Summary']['no_of_forwarded_notes']}} notifications to you.
    </p>

    <p>
        You can see a list of forwarded notes for your patients <a href="{{$data['sections']['Overall Summary']['link_to_notes_listing']}}">here</a>,
        including {{$data['sections']['Overall Summary']['no_of_forwarded_emergency_notes']}} notifications that your patient is in the ER/Hospital.
    </p>

</div>

@endif

@if(isset($data['sections']['Enrollment Summary']))

    <?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
<dl class="dl-horizontal">
    <h4>
        <dt>Current Cumulative:</dt>
        <dt>Enrolled <span style="color: green"> {{$data['sections']['Enrollment Summary'][$currentMonth]['added'] ?? 'N/A'}} </span></dt>
        <dt>Withdrawn <span style="color: darkred"> {{$data['sections']['Enrollment Summary'][$currentMonth]['withdrawn'] ?? 'N/A'}} </span></dt>
        <dt>Paused<span style="color: darkorange"> {{$data['sections']['Enrollment Summary'][$currentMonth]['paused'] ?? 'N/A'}} </span></dt>

    </h4>
</dl>


<table class="table table-bordered">
    <tr>
        <th>Type</th>
        <th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>
    </tr>

    @foreach($data['sections']['Enrollment Summary'] as $key => $value)
        <tr>

            <td>{{''}}</td>
            <td>{{$value['withdrawn']}}</td>
            <td>{{$value['paused']}}</td>
            <td>{{$value['added']}}</td>
            <td>{{$value['billable']}}</td>

        </tr>
    @endforeach
</table>

@endif
