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

@if(isset($editMode) && $editMode != false)
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
@endif
<style>
    .cp-section {
        border-bottom:3px solid #50B2E2;
    }
</style>

<div class="col-sm-12 cp-item" style="">
    @if(isset($editMode) && $editMode != false)
        @include('partials.carePlans.itemEdit')
    @else
        {{-- VIEW ONLY:
        <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br /> --}}
    @endif

    @if ($planItem->ui_fld_type == 'SELECT')
            <div class="form-group">
                <div class="form-item col-sm-12">
                    <div class="checkbox text-medium-big">
                        <div class="radio-inline"><input id="carePlanItem{{ $planItem->id }}" name="item|{{ $planItem->id }}" value="Active" class="itemTrigger" data-toggle="collapse" data-target="#{{ $planItem->id }}_modal_contentclone" type="checkbox"
                            @if ($planItem->meta_value == 'Active')
                                checked="checked"
                            @endif
                            >
                            <label for="carePlanItem{{ $planItem->id }}">
                            <span></span>{{ $planItem->careItem->display_name }}</label>
                        </div>
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