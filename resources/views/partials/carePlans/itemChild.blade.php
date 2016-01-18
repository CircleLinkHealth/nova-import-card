@if(isset($editMode) && $editMode != false && $planItemChild->ui_row_start > 0) start row @endif
{!! $planItemChild->ui_row_start > 0 ? '<div class="row item-row" style="">' : '' !!}

@if ($planItemChild->ui_col_start > 0)
    <div class="col-sm-{!! $planItemChild->ui_col_start !!} cp-item-child" style="">
@endif

@if(isset($editMode) && $editMode != false)
    @include('partials.carePlans.itemChildEdit')
@else
    {{-- VIEW ONLY:
    <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br /> --}}
@endif

@if ($planItemChild->ui_fld_type == 'SELECT')
    <input id="Hypertension" name="CHECK_STATUS|27|39|status" value="Active" checked="checked" class="itemTrigger" data-toggle="collapse" data-target="#39_modal_contentclone" type="checkbox"><label for="Hypertension"><span> </span>{{ $planItemChild->careItem->display_name }}</label><br />
@elseif ($planItemChild->ui_fld_type == 'RADIO_MULT')
    <?php
    //$modal_content .= $key . " "; // HIDE CONTACT DAYS
    $modal_content = '<div class="row"><div class="form-group">
        <div class="form-item col-sm-12">'; // HIDE CONTACT DAYS
    for ($i = 1; $i < 8; $i++) {
        if ($i == 1) {
            $day = 'M';
        }
        if ($i == 2) {
            $day = 'T';
        }
        if ($i == 3) {
            $day = 'W';
        }
        if ($i == 4) {
            $day = 'T';
        }
        if ($i == 5) {
            $day = 'F';
        }
        if ($i == 6) {
            $day = 'S';
        }
        if ($i == 7) {
            $day = 'S';
        }
        $status = '';
        if (strpos($planItem->meta_value, strval($i)) > -1) $status = ' checked="checked"';
        // $modal_content .= '<input type="checkbox" name="test" id="" value="1">';
        $name = $planItem->careItem->name;
        $modal_content .= '<div class="radio-inline"><input type="checkbox" id="' . $name . $i . '" name="' . $name . '[]" value="' . $i . '" ' . $status . '><label for="' . $name . $i . '"><span> </span>&nbsp;' . $day . '</label></div>';  // HIDE CONTACT DAYS
    }
    $modal_content .= '</div></div></div>';
    ?>
    {{ $planItemChild->careItem->display_name }}
    {!! $modal_content !!}
@elseif ($planItemChild->ui_fld_type == 'INPUT')
    {{ $planItemChild->careItem->display_name }}<br>
    <input name="item|{{ $planItemChild->id }}" value="{{ $planItemChild->meta_value }}" placeholder="" type="text">
@elseif ($planItemChild->ui_fld_type == 'CHECK')
    <div class="row">
        <div class="form-group">
            <div class="form-item col-sm-12">
                <div class="checkbox text-medium-big">
                    <div class="radio-inline"><input id="carePlanItem{{ $planItemChild->id }}" name="item|{{ $planItemChild->id }}" value="Active" class="itemTrigger" data-toggle="collapse" data-target="#{{ $planItemChild->id }}_modal_contentclone" type="checkbox"
                        @if ($planItemChild->meta_value == 'Active')
                             checked="checked"
                        @endif
                        >
                        <label for="carePlanItem{{ $planItemChild->id }}">
                            <span></span>{{ $planItemChild->careItem->display_name }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif ($planItemChild->ui_fld_type == 'TEXTAREA')
    @if($planItemChild->careItem->display_name == 'Details')
    <!-- Trigger the modal with a button -->
        <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#carePlanItem{{ $planItem->careItem->id }}Detail">Instructions</button>

        <!-- Modal -->
        <div id="carePlanItem{{ $planItem->careItem->id }}Detail" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">{{ $planItem->careItem->display_name }} :: Details</h4>
                    </div>
                    <div class="modal-body">
                        <textarea id="item{{ $planItemChild->id }}modal" name="item|{{ $planItemChild->id }}">{{ $planItemChild->meta_value }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>
    @endif
@else
    {{ $planItemChild->careItem->display_name }}
@endif



@if ($planItemChild->ui_col_end > 0)
</div>
@endif
{!! $planItemChild->ui_row_end > 0 ? '</div>' : '' !!}
@if(isset($editMode) && $editMode != false && $planItemChild->ui_row_end > 0) end row<br /> @endif
{!! $planItemChild->ui_row_end > 0 ? '<br />' : '' !!}