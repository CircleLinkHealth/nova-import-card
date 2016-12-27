<h3>Financial Performance</h3>

<?php $currentMonth = Carbon\Carbon::now()->format('F Y') ?>
<ul class="">
    <h5>
        <li>CCM Revenue to date: <span
                    style="color: green"> {{$data[$financialSection]['revenue_so_far'] ?? 'N/A'}} </span>
        </li>
        <li>CCM Profit to date: <span
                    style="color: green"> {{$data[$financialSection]['profit_so_far'] ?? 'N/A'}} </span>
        </li>
        <li>Patients billed to date:<span
                    style="color: #50b2e2"> {{$data[$financialSection]['billed_so_far'] ?? 'N/A'}} </span>
        </li>

    </h5>
</ul>


<table class="table table-bordered">
    <tr>
        <td>Type</td>
        <th>{{\Carbon\Carbon::now()->subMonths(1)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(2)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(3)->format('F')}}</th>
        <th>{{\Carbon\Carbon::now()->subMonths(4)->format('F')}}</th>

    </tr>

    @foreach($data[$financialSection]['historical'] as $key => $values)
        <tr>
            <td>{{$key}}</td>
            @foreach($values as $value)
                <td>{{$value}}</td>
            @endforeach
        </tr>
    @endforeach
</table>