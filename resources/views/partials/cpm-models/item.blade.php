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
                <div class="radio-inline">
                    <input id="{{ $section->name }}-{{$i}}{{$item->id}}"
                           name="{{ $section->name }}[]"
                           value="{{ $item->id }}" class="itemTrigger" data-toggle="collapse"
                           data-target="#collapseItem-{{ $section->name }}-{{$i}}{{$item->id}}"
                           type="checkbox" {{ in_array($item->id, $section->patientItemIds) ? 'checked=checked' : '' }}>
                    <label for="{{ $section->name }}-{{$i}}{{$item->id}}">
                        <span></span>{{ $item->name }}</label>
                </div>
            </div>
        </div>

        @if (isset($buttonLabel))
            <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                <button type="button"
                        class="btn btn-default btn-xs btn-monitor collapse {{ in_array($item->id, $section->patientItemIds) ? 'in' : '' }} text-right"
                        id="collapseItem-{{ $section->name }}-{{$i}}{{$item->id}}" data-toggle="modal"
                        data-target="#{{ $section->name }}-{{$i}}{{$item->id}}-Detail"
                        style="margin-top:0px;    margin-bottom: 0px;">{{ $buttonLabel }}</button>

                <!-- Modal -->
                <div id="{{ $section->name }}-{{$i}}{{$item->id}}-Detail" class="modal fade text-left"
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
                                <textarea id="item-{{ $section->name }}-{{$i}}{{$item->id}}-modal"
                                          {{--For the time being we don't wanna post those--}}
                                          {{--name="instructions-{{ $section->name }}[]"--}}
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