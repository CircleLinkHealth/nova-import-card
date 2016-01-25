<div class="row">
    <button type="button" class="btn btn-success btn-xs" data-toggle="collapse" href="#collapseItemEdit{{ $planItem->id }}" aria-expanded="false" aria-controls="collapseItemEdit{{ $planItem->id }}">Edit</button>
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-danger btn-xs section-reload" section="{{ $careSection->id }}">Remove</a>
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-orange btn-xs section-reload" section="{{ $careSection->id }}">Add Child Item</a>
</div>
<div class="collapse" id="collapseItemEdit{{ $planItem->id }}" style="background:#ccc;">
    [EYE:{{ $i+1 .' of '.$careSection->carePlanItems->count() }}]<br />
    [CarePlanItem:{{ $planItem->id }}]<br />
    [ui_sort:{{ $planItem->ui_sort }}]<br />
    [ui_fld_type:{{ $planItem->ui_fld_type }}]<br />
    [ui_row_start:{{ $planItem->ui_row_start }}]<br />
    [ui_row_end:{{ $planItem->ui_row_end }}]<br />
    [ui_col_start:{{ $planItem->ui_col_start }}]<br />
    [ui_default:{{ $planItem->ui_default }}]<br />Other
    [obs_key:{{ $planItem->careItem->obs_key }}]<br />
</div>