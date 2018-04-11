@push('styles')
    <style>
        .cp-item-row {
            border:0px solid blue;
        }
        .cp-item {
            border:0px solid #222;
        }
        .cp-item-child {
            border:0px solid #444;
        }
        .cp-section {
            border:0px solid #666;
        }
    </style>
@endpush

@if(isset($editMode) && $editMode != false)
    @push('styles')
        <style>
            .cp-item-row {
                border:1px solid blue;
            }
            .cp-item {
                border:1px solid #222;
            }
            .cp-item-child {
                border:1px solid #444;
            }
            .cp-section {
                border:1px solid #666;
            }
        </style>
    @endpush
@endif
@push('styles')
    <style>
        .cp-section {
            border-bottom:3px solid #50B2E2;
        }
    </style>
@endpush

<div class="col-sm-12X cp-itemX" style="">
    @if(isset($editMode) && $editMode != false)
        @include('partials.carePlans.itemEdit')
    @else
        {{-- VIEW ONLY:
        <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br /> --}}
    @endif

    @if ($planItem->ui_fld_type == 'SELECT')
        {{-- show details button on right if present --}}
        <?php
        $detailChildItem = null;
        foreach($planItem->children as $planItemChild) {
            if( $planItemChild->ui_fld_type == 'TEXTAREA') {
                $detailChildItem = $planItemChild;
                $detailChildItemLabel = 'Instructions';
                if($careSection->name == 'Additional Information') {
                    $detailChildItemLabel = 'Details';
                }
                // change to details for certain section
            }
        }
        ?>
        <div class="form-group">
            <div class="form-item col-sm-{{ $detailChildItem ? '12' : '12' }}">
                <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                    <div class="radio-inline"><input id="carePlanItem{{ $planItem->id }}" name="item|{{ $planItem->id }}" value="Active" class="itemTrigger" data-toggle="collapse" data-target="#collapseItem{{ $planItem->id }}" type="checkbox" {{ $planItem->meta_value == 'Active' ? 'checked=checked' : '' }}>
                        <label for="carePlanItem{{ $planItem->id }}">
                        <span></span>{{ $planItem->careItem->display_name }}</label>
                    </div>
                </div>
            </div>
            @if ($detailChildItem)
                    <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                        <button type="button" class="btn btn-default btn-xs btn-monitor collapse {{ $planItem->meta_value == 'Active' ? 'in' : '' }} text-right" id="collapseItem{{ $planItem->id }}" data-toggle="modal" data-target="#carePlanItem{{ $planItem->careItem->id }}Detail" style="margin-top:0px;    margin-bottom: 0px;">{{ $detailChildItemLabel }}</button>

                        <!-- Modal -->
                        <div id="carePlanItem{{ $planItem->careItem->id }}Detail" class="modal fade text-left" role="dialog">
                            <div class="modal-dialog modal-lg">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header" style="background:#50B2E2;">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title" style="color:#fff;">{{ $planItem->careItem->display_name }} : {{ $detailChildItemLabel }}</h4>
                                    </div>
                                    <div class="modal-body">
                                        <textarea id="item{{ $detailChildItem->id }}modal" name="item|{{ $detailChildItem->id }}" style="height: 400px;">{{ $detailChildItem->meta_value }}</textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default btn-primary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
            @endif
        </div>
    @endif

    @if (!is_null($planItem->children))
        <div class="collapse {{ $planItem->meta_value == 'Active' ? 'in' : '' }}" id="collapseItem{{ $planItem->id }}">
            @foreach($planItem->children as $planItemChild)
                @if( $planItemChild->ui_fld_type != 'TEXTAREA')
                    @include('partials.carePlans.itemChild')
                @endif
            @endforeach
        </div>
    @endif
</div>