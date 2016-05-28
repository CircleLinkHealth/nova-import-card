{{--This is the admin careplan section edit--}}
<div class="row">
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-primary btn-xs section-reload" section="{{ $careSection->id }}">Refresh</a>
    <button type="button" class="btn btn-success btn-xs" data-toggle="collapse" href="#collapseSectionEdit{{ $careSection->id }}" aria-expanded="false" aria-controls="collapseSectionEdit{{ $careSection->id }}">Edit</button>
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-danger btn-xs section-reload" section="{{ $careSection->id }}">Remove</a>
    <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-orange btn-xs section-reload" section="{{ $careSection->id }}">Add Item</a>
</div>
<a class="" role="" data-toggle="collapse" href="#collapseSection{{ $careSection->id }}" aria-expanded="false" aria-controls="collapseSection{{ $careSection->id }}">
    <h1>{{ $careSection->display_name }}</h1>
</a>
<div class="collapse" id="collapseSectionEdit{{ $careSection->id }}" style="background:#ccc;">
    [status:{{ $careSection->status }}]<br />
    [ui_sort:{{ $careSection->ui_sort }}]<br />
</div>