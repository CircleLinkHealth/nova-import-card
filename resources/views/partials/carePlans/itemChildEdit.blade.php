<div class="row">
    <button type="button" class="btn btn-success btn-xs" data-toggle="collapse" href="#itemEdit{{ $planItemChild->id }}" aria-expanded="false" aria-controls="itemEdit{{ $planItemChild->id }}">Edit</button>
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-danger btn-xs section-reload" section="{{ $careSection->id }}">Remove</a>
</div>
<div class="collapse" id="itemEdit{{ $planItemChild->id }}" style="background:#ccc;">
    [ui_sort:{{ $planItemChild->ui_sort }}]<br />
    [ui_fld_type:{{ $planItemChild->ui_fld_type }}]<br />
    [ui_row_start:{{ $planItemChild->ui_row_start }}]<br />
    [ui_row_end:{{ $planItemChild->ui_row_end }}]<br />
    [ui_col_start:{{ $planItemChild->ui_col_start }}]<br />
    [ui_default:{{ $planItemChild->ui_default }}]<br />
    [ui_sort:{{ $planItemChild->ui_sort }}]<br />
</div>