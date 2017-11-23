<input type="hidden" name="activityName" id="activityName" value="@yield('activity')">

<?php
use App\User;use Carbon\Carbon;


?>

@push('prescripts')
    <script>

        var timeTrackerInfo = {
            "patientId": '<?php echo $patientId; ?>' === '' ? '0' : '<?php echo $patientId; ?>',
            "providerId": '<?php echo Auth::user()->id ?>',
            "totalTime": ((monthlyTime) => {
                            if (monthlyTime) {
                                const split = monthlyTime.split(':');
                                const seconds = Number(split[2]), minutes = Number(split[1]), hours = Number(split[0]);
                                return seconds +
                                        (minutes * 60) + 
                                        (hours * 60 * 60);
                            }
                            return 0;
                        })(document.querySelector('[data-monthly-time]') ? document.querySelector('[data-monthly-time]').getAttribute('data-monthly-time') : null),
            "wsUrl": "{{ env('WS_URL') }}",
            "programId": '<?php echo $patientProgramId; ?>',
            "urlFull": '<?php echo Request::url(); ?>',
            "urlShort": '<?php echo $urlShort; ?>',
            "ipAddr": '<?php echo $ipAddr; ?>',
            "activity": (document.getElementById('activityName') || { value: '' }).value,
            "title": '<?php echo $title; ?>',
            "submitUrl": '<?php echo URL::route("api.pagetracking"); ?>',
            "startTime": '<?php echo Carbon::now()->subSeconds(8)->toDateTimeString(); ?>'//,
            //"disabled": ('{{!$enableTimeTracking}}' == '1')
        }
    </script>
@endpush

@if ($enableTimeTracking)
    @push('scripts')
        <script></script>
    @endpush
@endif