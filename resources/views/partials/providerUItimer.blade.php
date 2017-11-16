<input type="hidden" name="activityName" id="activityName" value="@yield('activity')">

<?php
use App\User;use Carbon\Carbon;

if (!isset($activity)) {
    $activity = 'Undefined';
}

$title = Route::currentRouteName();

$ipAddr = Request::ip();

$requestUri = Request::getRequestUri();
$pieces = explode("?", $requestUri);
$urlShort = $pieces[0];

$enableTimeTracking = auth()->user()->isCCMCountable() && !isset($disableTimeTracking);

// disable if login
if (strpos($requestUri, 'login') !== false) {
//    $enableTimeTracking = false;
}

// set patient vars
$patientId = '';
$patientProgramId = '';
if (isset($patient) && !empty($patient) && is_a($patient, App\User::class)) {
    $patientId = $patient->id;
    $patientProgramId = $patient->program_id;
} elseif (isset($patient) && !empty($patient) && is_a($patient, App\Patient::class)) {
    $patientId = $patient->user_id;
    $patientProgramId = $patient->user->program_id;
}
?>

@push('prescripts')
    <script>
        var timeTrackerInfo = {
            "patientId": '<?php echo $patientId; ?>' === '' ? '0' : '<?php echo $patientId; ?>',
            "providerId": '<?php echo Auth::user()->id ?>',
            "totalTime": ((monthlyTime) => {
                            if (monthlyTime) {
                                const split = monthlyTime.split(':');
                                const minutes = Number(split[2]), hours = Number(split[1]), days = Number(split[0]);
                                return (minutes * 60) + 
                                        (hours * 60 * 60) + 
                                        (days * 24 * 60 * 60);
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
            "startTime": '<?php echo Carbon::now()->subSeconds(8)->toDateTimeString(); ?>'
        }
    </script>
@endpush

@if ($enableTimeTracking)
    @push('scripts')
        <script></script>
    @endpush
@endif