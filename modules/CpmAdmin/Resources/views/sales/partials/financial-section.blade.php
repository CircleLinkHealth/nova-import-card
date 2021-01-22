<h3 style="text-align: center">Financial Performance</h3>

<!---
Inline CSS prevents loss of table borders in emails
-->

{{--<h5>--}}
{{--CCM Revenue to date: <span--}}
{{--style="color: green"> {{$data[$financialSection]['revenue_so_far'] ?? 'N/A'}} </span><br />--}}

{{--CCM Profit to date: <span--}}
{{--style="color: green"> {{$data[$financialSection]['profit_so_far'] ?? 'N/A'}} </span><br />--}}

{{--Patients billed to date:<span--}}
{{--style="color: #50b2e2"> {{$data[$financialSection]['billed_so_far'] ?? 'N/A'}} </span><br />--}}


{{--</h5>--}}


<table class="table table-bordered myTable" style="border-collapse: collapse;border: 1px solid black; padding: 2px">
    <tr>
        <td style="width: 25%; border: 1px solid black; padding: 2px"></td>
        <th style="width: 25%; border: 1px solid black; padding: 2px">{{$data['end']->format('F') . ' to Date'}}</th>
        <th style="width: 25%; border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(1)->format('F')}}</th>
        <th style="width: 25%; border: 1px solid black; padding: 2px">{{$data['end']->copy()->subMonthNoOverflow(2)->format('F')}}</th>

    </tr>

    @foreach($data[$financialSection]['historical'] as $key => $values)
        <tr>
            <td style="width: 30%; border: 1px solid black; padding: 2px; text-align: center"><strong>{{$key}}</strong></td>
            @foreach($values as $value)
                <td style="border: 1px solid black; padding: 2px; text-align: center">{{$value}}</td>
            @endforeach
        </tr>
    @endforeach
</table>
<br/>