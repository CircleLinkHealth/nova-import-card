<input type=hidden name="careSections[]" value="{{ $careSection->id }}">
<div class="row">
<div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 cp-section"  id="section{{ $careSection->id }}">
    @if(isset($editMode) && $editMode != false)
        @include('partials.carePlans.sectionEdit')
    @else
        {{-- VIEW ONLY: --}}
        <a class="" role="" data-toggle="collapse" href="#collapseSection{{ $careSection->id }}" aria-expanded="true" aria-controls="collapseSection{{ $careSection->id }}">
            <h4>{{ $careSection->display_name }}</h4>
        </a>
    @endif
    @if(!empty($careSection->carePlanItems))
        <?php $i=0; ?>
        <?php $r=1; ?>
        <div class="collapse in" id="collapseSection{{ $careSection->id }}">
            @foreach($careSection->carePlanItems as $planItem)
                @if ($planItem->careItem->display_name != '')
                    <?php
                    // column math
                    $num_items_total = count($careSection->carePlanItems);
                    $num_items_per_column = round($num_items_total / 2);
                    ?>

                    <!-- output column containers if needed -->
                    @if($i == 0)
                        <div class="form-block form-block--left col-md-6">
                        <div class="row">';
                    @elseif($i == $num_items_per_column)
                        <div class="form-block form-block--right col-md-6">
                        <div class="row">
                    @endif

                    <!-- include item -->
                    @if($i % 2 == 0)
                        @if(isset($editMode) && $editMode != false) START ROW {{ $r }} @endif
                        <div class="row">
                    @endif
                    <div class="col-sm-12">
                        {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                        {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}
                        @include('partials.carePlans.item')
                        {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                        {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                    @if( ($i % 2 != 0) || ($careSection->carePlanItems->count() == ($i+1)) )
                        @if(isset($editMode) && $editMode != false) END ROW {{ $r }} @endif
                        </div>
                        <?php $r++; ?>
                    @endif
                    </div>

                    <!-- output column containers if needed -->
                    @if($i==($num_items_per_column-1))
                        </div></div>
                    @elseif($i == ($num_items_total-1))
                        </div></div>
                    @endif
                @endif
                <?php $i++; ?>
            @endforeach
        </div>
    @endif

</div>
</div>