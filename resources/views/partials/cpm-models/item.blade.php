<style>
    .cp-item-row {
        border: 0px solid blue;
    }

    .cp-item {
        border: 0px solid #222;
    }

    .cp-item-child {
        border: 0px solid #444;
    }

    .cp-section {
        border: 0px solid #666;
    }
</style>

@if(isset($editMode) && $editMode != false)
    <style>
        .cp-item-row {
            border: 1px solid blue;
        }

        .cp-item {
            border: 1px solid #222;
        }

        .cp-item-child {
            border: 1px solid #444;
        }

        .cp-section {
            border: 1px solid #666;
        }
    </style>
@endif
<style>
    .cp-section {
        border-bottom: 3px solid #50B2E2;
    }
</style>

{{--@if (1 < 0)--}}
    <div class="col-sm-12X cp-itemX" style="">
        @if(isset($editMode) && $editMode != false)
            @include('partials.carePlans.itemEdit')
        @else
            {{-- VIEW ONLY:
            <strong>{{ $item->meta_key . ' = ' . $item->patient_id }}</strong><br /> --}}
        @endif

        {{-- show details button on right if present --}}
        @if(isset($item->cpmInstructions[0]))
            <?php
            $buttonLabel = 'Instructions';
            ?>
        @endif

            <div class="form-group">
            <div class="form-item col-sm-12">
                <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                    <div class="radio-inline"><input id="carePlanItem-{{ $itemType }}-{{ $item->id }}"
                                                     name="item|-{{ $itemType }}-{{ $item->id }}"
                                                     value="Active" class="itemTrigger" data-toggle="collapse"
                                                     data-target="#collapseItem-{{ $itemType }}-{{ $item->id }}"
                                                     type="checkbox" {{ $item->patient_id == 'Active' ? 'checked=checked' : '' }}>
                        <label for="carePlanItem-{{ $itemType }}-{{ $item->id }}">
                            <span></span>{{ $item->name }}</label>
                    </div>
                </div>
            </div>

            @if (isset($buttonLabel))
                <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                    <button type="button"
                            class="btn btn-default btn-xs btn-monitor collapse {{ $item->patient_id == 'Active' ? 'in' : '' }} text-right"
                            id="collapseItem-{{ $itemType }}-{{ $item->id }}" data-toggle="modal"
                            data-target="#carePlanItem-{{ $itemType }}-{{ $item->id }}Detail"
                            style="margin-top:0px;    margin-bottom: 0px;">{{ $buttonLabel }}</button>

                    <!-- Modal -->
                    <div id="carePlanItem-{{ $itemType }}-{{ $item->id }}Detail" class="modal fade text-left"
                         role="dialog">
                        <div class="modal-dialog modal-lg">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header" style="background:#50B2E2;">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title" style="color:#fff;">{{ $item->name }}
                                        : {{ $buttonLabel }}</h4>
                                </div>
                                <div class="modal-body">
                                <textarea id="item-{{ $itemType }}-{{ $item->id }}modal"
                                          name="item|-{{ $itemType }}-{{ $item->id }}"
                                          style="height: 400px;">{{ $item->cpmInstructions[0]->name }}</textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default btn-primary" data-dismiss="modal">Close
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
{{--@endif--}}