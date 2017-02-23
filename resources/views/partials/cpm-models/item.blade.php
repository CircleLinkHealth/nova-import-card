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

    {{-- show an instructions button, only if the cpm item has a default instruction --}}
    {{-- this way we will not render an instruction box for cpm items that do not have it (such as lifestyles) --}}
    @if($item->pivot->has_instruction)
        <?php
        $buttonLabel = 'Instructions';
        ?>
    @endif

    <div class="form-group">
        <div class="form-item col-sm-12">
            <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                <div class="radio-inline">
                    <input id="{{ $section->name }}-{{$i}}{{$item->id}}"
                           name="{{ $section->name }}[{{ $item->id }}]"
                           value="{{ $item->id }}" class="itemTrigger" data-toggle="collapse"
                           data-target="#collapseItem-{{ $section->name }}-{{$i}}{{$item->id}}"
                           type="checkbox" {{ in_array($item->id, $section->patientItemIds) ? 'checked=checked' : '' }}>
                    <label for="{{ $section->name }}-{{$i}}{{$item->id}}">
                        <span></span>{{ $item->name }}</label>
                </div>
            </div>
        </div>

        @if(isset($buttonLabel))

            {{--Figure out which instruction to show--}}
            <?php $instructionName = ' '; ?>

            @if(in_array($item->id, $section->patientItemIds)
                && isset($section->patientItems[$item->id])
                && !empty($section->patientItems[$item->id])
                && !empty($instructionId = $section->patientItems[$item->id]->pivot->cpm_instruction_id)
                && !empty($instruction = \App\Models\CPM\CpmInstruction::find($instructionId)))

                {{--if the user has their own instruction then use that--}}
                <?php $instructionName = $instruction->name; ?>

            @elseif(!empty($instruction = \App\Models\CPM\CpmInstruction::find($item->pivot->cpm_instruction_id)))

                {{--otherwise grap the cpmItem's default instruction--}}
                <?php $instructionName = $instruction->name; ?>

            @endif

            <div class="checkbox text-medium-big" style="margin-top:0px;    margin-bottom: 0px;">
                <button type="button"
                        class="btn btn-default btn-xs btn-monitor collapse {{ in_array($item->id, $section->patientItemIds) ? 'in' : '' }} text-right"
                        id="collapseItem-{{ $section->name }}-{{$i}}{{$item->id}}" data-toggle="modal"
                        data-target="#{{ $section->name }}-{{$i}}{{$item->id}}-Detail"
                        style="margin-top:0px;    margin-bottom: 0px;"><span><i class="glyphicon glyphicon-pencil"></i></span>
                </button>

                <!-- Modal -->
                <div id="{{ $section->name }}-{{$i}}{{$item->id}}-Detail" class="modal fade text-left"
                     role="dialog">
                    <div class="modal-dialog modal-md">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header" style="background:#50B2E2;">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" style="color:#fff;">{{ $item->name }}
                                    : {{ $buttonLabel }}</h4>
                            </div>
                            <div class="modal-body">
                                @if( $item->name == App\Models\CPM\CpmMisc::MEDICATION_LIST )
                                    @include('partials.ccd-models.items.medications')
                                @elseif( $item->name == App\Models\CPM\CpmMisc::OTHER_CONDITIONS )
                                    @include('partials.ccd-models.items.problems')
                                @elseif( $item->name == App\Models\CPM\CpmMisc::ALLERGIES )
                                    @include('partials.ccd-models.items.allergies')
                                @else
                                    <textarea id="item-{{ $section->name }}-{{$i}}{{$item->id}}-modal"
                                              name="instructions[{{ $section->name }}][{{ $item->id }}]"
                                              style="height: 400px;">{{ trim($instructionName) }}</textarea>
                                @endif
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

@if($section->name == 'cpmBiometrics')
    <div class="collapse {{ in_array($item->id, $section->patientItemIds) ? 'in' : '' }}"
         id="collapseItem-{{ $section->name }}-{{$i}}{{$item->id}}">

        @if($item->name == \App\Models\CPM\CpmBiometric::BLOOD_PRESSURE)
            @include('partials.cpm-models.biometrics.bloodPressure')
        @endif

        @if($item->name == \App\Models\CPM\CpmBiometric::BLOOD_SUGAR)
            @include('partials.cpm-models.biometrics.bloodSugar')
        @endif

        @if($item->name == \App\Models\CPM\CpmBiometric::SMOKING)
            @include('partials.cpm-models.biometrics.smoking')
        @endif

        @if($item->name == \App\Models\CPM\CpmBiometric::WEIGHT)
            @include('partials.cpm-models.biometrics.weight')
        @endif
    </div>
@endif