<style>
    table, td, th {
        border: 1px solid #ddd;
        text-align: left;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        padding: 15px;
    }
</style>

<h2>CircleLink Health</h2>
<h3>Itemized Session Details</h3>
<h4>{{$date_start}} to {{$date_end}}</h4>
<h4>{{$nurse_name}}</h4>
<h4>Billable Time: {{gmdate("H:i:s", $nurse_billable_time)}}</h4>
<h4>Invoice Amount: {{$total_billable_amount}}</h4>

<table>
    <tr>
        <th>Date</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Patient ID</th>
        <th>Duration</th>
    </tr>
    @for($i = 0; $i < count($data); $i++)
    <tr>
        <td>{{$data[$i]['Date']}}</td>
        <td>{{$data[$i]['Start Time']}}</td>
        <td>{{$data[$i]['End Time']}} </td>
        <td>{{$data[$i]['Patient']}}</td>
        <td>{{$data[$i]['Duration']}}</td>
    </tr>
    @endfor
</table>