@if($problems)
    @foreach($problems as $key => $value)
        <div class="patient-info__subareas">
            <div class="row">
                <div class="col-xs-12">
                    <h3 class="patient-summary__subtitles--subareas patient-summary--careplan">@if($key != App\Models\CPM\CpmMisc::OTHER_CONDITIONS){{'For '}}@endif
                        <?= $key; ?>:</h3>
                </div>
                <div class="col-xs-12">
                    <p><?= nl2br($value); ?></p>
                </div>
            </div>
        </div>
    @endforeach
@endif