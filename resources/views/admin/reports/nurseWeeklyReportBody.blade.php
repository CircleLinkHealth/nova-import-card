@forelse($data as $name => $report)
    <tr>
        <td class="zui-sticky-col">{{$name}}</td>
        @foreach($report as $reportPerDay)
            <td>{{$reportPerDay['scheduledCalls']}} </td>
            <td>{{$reportPerDay['actualCalls']}} </td>
            <td>{{$reportPerDay['successful']}} </td>
            <td>{{$reportPerDay['unsuccessful']}} </td>
            <td>{{$reportPerDay['actualHours']}} </td>
            <td>{{$reportPerDay['committedHours']}} </td>
            <td>{{array_key_exists('completionRate', $reportPerDay) ? $reportPerDay['completionRate'] : 'N/A'}}
                %
            </td>
            <td>{{array_key_exists('efficiencyIndex', $reportPerDay) ? $reportPerDay['efficiencyIndex'] : 'N/A'}}</td>
            <td>{{array_key_exists('caseLoadNeededToComplete' , $reportPerDay) ? $reportPerDay['caseLoadNeededToComplete' ] : 'N/A'}}</td>
            <td>{{array_key_exists('hoursCommittedRestOfMonth', $reportPerDay) ? $reportPerDay['hoursCommittedRestOfMonth'] : 'N/A'}}</td>
            @php
                $tdClass = '';
                if(array_key_exists('surplusShortfallHours', $reportPerDay)){
                    if($reportPerDay['surplusShortfallHours'] < 0 ){
                    $tdClass = 'red';
                    }elseif($reportPerDay['surplusShortfallHours'] > 0){
                     $tdClass = 'green';
                    }
                }
            @endphp
            <td>
                <span class="{{$tdClass}}">{{array_key_exists('surplusShortfallHours', $reportPerDay) ? $reportPerDay['surplusShortfallHours'] : 'N/A'}}</span>
            </td>
            <td style="border-right: solid 2px #000000;">{{array_key_exists('caseLoadComplete', $reportPerDay) ? $reportPerDay['caseLoadComplete'] : 'N/A'}}
                %
            </td>

        @endforeach
        @empty
            <div class="no-data">
                <h4>There are no data for this week</h4>
            </div>
        @endforelse
    </tr>
    <tr>
        <td class="zui-sticky-col" style="font-weight: bolder">Totals:</td>
        @foreach ($totals as $total => $totalsPerDay)
            @foreach($totalsPerDay as $totalsForDay)
                <td style="font-weight: bolder">{{$totalsForDay['scheduledCallsSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay['actualCallsSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay['successfulCallsSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay['unsuccessfulCallsSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay['actualHoursSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay['committedHoursSum']}}</td>
                <td style="font-weight: bolder">{{$totalsForDay->has('completionRate') ? $totalsForDay['completionRate'] : 'N/A'}}
                    %
                </td>
                <td style="font-weight: bolder">{{$totalsForDay->has('efficiencyIndex') ? $totalsForDay['efficiencyIndex'] : 'N/A'}}</td>
                <td style="font-weight: bolder">{{$totalsForDay->has('caseLoadNeededToComplete') ? $totalsForDay['caseLoadNeededToComplete' ] : 'N/A'}}</td>
                <td style="font-weight: bolder">{{$totalsForDay->has('hoursCommittedRestOfMonth') ? $totalsForDay['hoursCommittedRestOfMonth'] : 'N/A'}}</td>
                @php
                    $totalTdClass = '';
                        if($totalsForDay->has('surplusShortfallHours')){
                            if($totalsForDay['surplusShortfallHours'] < 0 ){
                            $totalTdClass = 'red';
                            }elseif($totalsForDay['surplusShortfallHours'] > 0){
                             $totalTdClass = 'green';
                            }
                        }
                @endphp
                <td style="font-weight: bolder"><span
                            class="{{$totalTdClass}}">{{$totalsForDay->has('surplusShortfallHours') ? $totalsForDay['surplusShortfallHours'] : 'N/A'}}</span>
                </td>
                <td style="font-weight: bolder; border-right: solid 2px #000000;">{{$totalsForDay->has('caseLoadComplete') ? $totalsForDay['caseLoadComplete'] : 'N/A'}}
                    %
                </td>
            @endforeach
        @endforeach
    </tr>