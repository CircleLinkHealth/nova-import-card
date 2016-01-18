<input type=hidden name="careSections[]" value="{{ $careSection->id }}">
<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 cp-section"  id="section{{ $careSection->id }}">
    @if(isset($editMode) && $editMode != false)
        @include('partials.carePlans.sectionEdit')
    @else
        {{-- VIEW ONLY: --}}
        <a class="" role="" data-toggle="collapse" href="#collapseSection{{ $careSection->id }}" aria-expanded="true" aria-controls="collapseSection{{ $careSection->id }}">
            <h4>{{ $careSection->display_name }}</h4>
        </a>
    @endif
    @if(!empty($careSection->planItems))
        <?php $i=0; ?>
        <?php $r=1; ?>
        <div class="collapse in" id="collapseSection{{ $careSection->id }}">
            @foreach($careSection->planItems as $planItem)
                @if ($planItem->careItem->display_name != '')
                    @if($i % 2 == 0)
                        @if(isset($editMode) && $editMode != false) START ROW {{ $r }} @endif
                        <div class="row">
                    @endif
                    <div class="col-sm-6">
                        {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                        {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}
                        @include('partials.carePlans.item')
                        {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                        {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                    @if( ($i % 2 != 0) || ($careSection->planItems->count() == ($i+1)) )
                        @if(isset($editMode) && $editMode != false) END ROW {{ $r }} @endif
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