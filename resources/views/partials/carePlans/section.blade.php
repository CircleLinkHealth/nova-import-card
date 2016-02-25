<input type=hidden name="careSections[]" value="{{ $careSection->id }}">
<div class="main-form-container col-lg-8 col-lg-offset-2 cp-section" style="border-right: 3px solid #50b2e2;border-left: 3px solid #50b2e2;">
    <div class="main-form-block main-form-horizontal main-form-primary-horizontalX col-md-12 Xcp-section"  id="section{{ $careSection->id }}">
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
        <?php $half=round(count($careSection->carePlanItems )/2); ?>

        <div class="form-block form-block--left col-md-6">
            @foreach($careSection->carePlanItems as $planItem)
            <?= ($i == $half ? "</div><div class='form-block form-block--right col-md-6'>" : "" ) ; ?>
            <div class="row">
                <div class="form-item col-sm-12" style="padding-left: 0px;">
                  @include('partials.carePlans.item')
                </div>
            </div>
            <?php $i++; ?>
            @endforeach
        </div>




        <div class="hidden collapse in" id="collapseSection{{ $careSection->id }}">
        <?php $i=0; ?>
            @foreach($careSection->carePlanItems as $planItem)
                @if ($planItem->careItem->display_name != '')
                    @if($i % 2 == 0)
                        @if(isset($editMode) && $editMode != false) START ROW {{ $r }} @endif
                        <div class="row">
                    @endif
                    <div class="col-sm-6"   @if($i % 2 != 0) 
                                            style="border-left: 1px solid #ccc;" 
                                            @else
                                            style="border-right:1px solid #ccc;" 
                                            @endif>
                        {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                        {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}<!-- " -->
                        @include('partials.carePlans.item')
                        {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                        {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                    @if( ($i % 2 != 0) || ($careSection->carePlanItems->count() == ($i+1)) )
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
</div>