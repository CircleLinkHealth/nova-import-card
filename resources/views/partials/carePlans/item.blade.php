@if(isset($editMode) && $editMode != false)
    <style>
        .item-row {
            border:1px solid blue;
        }
        .item {
            border:1px solid #222;
        }
        .item-child {
            border:1px solid #444;
        }
    </style>
@else
    <style>
        .item-row {
            border:0px solid blue;
        }
        .item {
            border:0px solid #222;
        }
        .item-child {
            border:0px solid #444;
        }
    </style>
@endif


<div class="col-sm-12 item" style="">
    @if(isset($editMode) && $editMode != false)
        @include('partials.carePlans.itemEdit')
    @else
        {{-- VIEW ONLY:
        <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br /> --}}
    @endif

    @if ($planItem->ui_fld_type == 'SELECT')
            <div class="form-group">
                <div class="form-item col-sm-12">
                    <div class="checkbox text-medium-big"><div class="radio-inline"><input name="CHECK_STATUS|27|39|status" value="Inactive" type="hidden"><input id="carePlanItem{{ $planItem->careItem->id }}" name="CHECK_STATUS|27|39|status" value="Active" class="itemTrigger" data-toggle="collapse" data-target="#{{ $planItem->careItem->id }}_modal_contentclone" type="checkbox"><label for="carePlanItem{{ $planItem->careItem->id }}"><span> </span>{{ $planItem->careItem->display_name }}</label></div><button style="display: none;" type="button" class="btn btn-default btn-xs btn-monitor text-right" data-toggle="modal" id="carePlanItem{{ $planItem->careItem->id }}Detail" data-target="#{{ $planItem->careItem->id }}_modalModal">Instructions</button>
                    </div>
                </div>
            </div>
    @endif

    @if (!is_null($planItem->children))
        @foreach($planItem->children as $planItemChild)

            @include('partials.carePlans.itemChild')

        @endforeach
    @endif
</div>