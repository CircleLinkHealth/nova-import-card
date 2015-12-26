<div class="col-sm-12" style="border:1px solid #222;">

    @include('partials.carePlans.itemEdit')

    <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br />

    @if ($planItem->ui_fld_type == 'SELECT')
        <input id="Hypertension" name="CHECK_STATUS|27|39|status" value="Active" checked="checked" class="itemTrigger" data-toggle="collapse" data-target="#39_modal_contentclone" type="checkbox"><label for="Hypertension"><span> </span><h2> {{ $planItem->careItem->display_name }} </h2></label><br />
    @endif

    @if (!is_null($planItem->children))
        @foreach($planItem->children as $planItemChild)
            {!! $planItemChild->ui_row_start > 0 ? 'start row<div class="row" style="border:1px solid blue;">' : '' !!}
            @if ($planItemChild->ui_col_start > 0)
                <div class="col-sm-{!! $planItemChild->ui_col_start !!}" style="border:1px solid #444;">
            @endif

            @include('partials.carePlans.itemChild')




            @if ($planItemChild->ui_fld_type == 'SELECT')
                <input id="Hypertension" name="CHECK_STATUS|27|39|status" value="Active" checked="checked" class="itemTrigger" data-toggle="collapse" data-target="#39_modal_contentclone" type="checkbox"><label for="Hypertension"><span> </span><h3>{{ $planItemChild->careItem->display_name }}</h3></label><br />
            @elseif ($planItemChild->ui_fld_type == 'RADIO_MULT')
                <?php
                //$modal_content .= $key . " "; // HIDE CONTACT DAYS
                $modal_content = '<div class="row">'; // HIDE CONTACT DAYS
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
                $modal_content .= '</div>';
                ?>
                <h3>{{ $planItemChild->careItem->display_name }}</h3>
                {!! $modal_content !!}
            @else
                <h3>{{ $planItemChild->careItem->display_name }}</h3>
            @endif



            @if ($planItemChild->ui_col_end > 0)
                </div>
            @endif
            {!! $planItemChild->ui_row_end > 0 ? '</div>end row<br />' : '' !!}
        @endforeach
    @endif
</div>