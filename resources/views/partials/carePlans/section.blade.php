<div class="col-sm-12" id="section{{ $careSection->id }}" style="border:2px solid #111;margin-top:20px;">
    @include('partials.carePlans.sectionEdit')
    {{-- VIEW ONLY:
    <a class="" role="" data-toggle="collapse" href="#collapseSection{{ $careSection->id }}" aria-expanded="false" aria-controls="collapseSection{{ $careSection->id }}">
    <h1>{{ $careSection->display_name }}</h1>
</a>
    --}}
    @if(!empty($careSection->planItems))
        <?php $i=0; ?>
        <?php $r=1; ?>
        <div class="collapse" id="collapseSection{{ $careSection->id }}">
            @foreach($careSection->planItems as $planItem)
                @if ($planItem->careItem->display_name != '')
                    @if($i % 2 == 0)
                        START ROW {{ $r }}
                        <div class="row">
                    @endif
                    <div class="col-sm-6">
                        {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                        {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}
                        @include('partials.carePlans.item')
                        {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                        {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                    @if( ($i % 2 != 0) || ($careSection->planItems->count() == ($i+1)) )
                        END ROW {{ $r }}
                        </div>
                        <?php $r++; ?>
                    @endif
                    </div>
                @endif
                <?php $i++; ?>
            @endforeach
        </div>
    @endif

</div>