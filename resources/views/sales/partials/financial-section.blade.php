<h3 style="text-align: center">Financial Performance</h3>

<?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
{{--<h5>--}}
{{--CCM Revenue to date: <span--}}
{{--style="color: green"> {{$data[$financialSection]['revenue_so_far'] ?? 'N/A'}} </span><br />--}}

{{--CCM Profit to date: <span--}}
{{--style="color: green"> {{$data[$financialSection]['profit_so_far'] ?? 'N/A'}} </span><br />--}}

{{--Patients billed to date:<span--}}
{{--style="color: #50b2e2"> {{$data[$financialSection]['billed_so_far'] ?? 'N/A'}} </span><br />--}}


{{--</h5>--}}


<table class="table table-bordered">
    <tr>
        <td></td>
        <th>{{\Carbon\Carbon::now()->format('F') . ' to Date'}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>

    </tr>

    @foreach($data[$financialSection]['historical'] as $key => $values)
        <tr>
            <td style="width: 30%"><strong>{{$key}}</strong></td>
            @foreach($values as $value)
                <td>{{$value}}</td>
            @endforeach
        </tr>
    @endforeach
</table>
<br/>