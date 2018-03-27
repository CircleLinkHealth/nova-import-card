<?php
use App\Patient;
?>
<input type="hidden" name="activityName" id="activityName" value="@yield('activity')">

@if (isset($useOldTimeTracker) && $useOldTimeTracker)
    @include('partials.providerUIOldTimer')

    <script>
        var timeTrackerInfo = {
            "totalTime": (function (monthlyTime) {
                            if (monthlyTime) {
                                var split = monthlyTime.split(':');
                                var seconds = Number(split[2]), minutes = Number(split[1]), hours = Number(split[0]);
                                return seconds +
                                        (minutes * 60) +
                                        (hours * 60 * 60);
                            }
                            return 0;
                        })(document.querySelector('[data-monthly-time]') ? document.querySelector('[data-monthly-time]').getAttribute('data-monthly-time') : null)
        }
    </script>
@else
    @if ($enableTimeTracking)
        @push('prescripts')
        <?php
        if (isset($patient)) {
            $patientId = $patient->id;

            if (is_a($patient, Patient::class)) {
                $patientId = optional($patient->user()->first())->id;
            }
        }
        $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
        ?>
        <script>

            var timeTrackerInfo = {
                "patientId": '{{$patientId}}' === '' ? '0' : '{{$patientId}}',
                "providerId": '{{Auth::user()->id}}',
                "totalTime": (function (monthlyTime) {
                                if (monthlyTime) {
                                    var split = monthlyTime.split(':');
                                    var seconds = Number(split[2]), minutes = Number(split[1]), hours = Number(split[0]);
                                    return seconds +
                                            (minutes * 60) +
                                            (hours * 60 * 60);
                                }
                                return 0;
                            })(document.querySelector('[data-monthly-time]') ? document.querySelector('[data-monthly-time]').getAttribute('data-monthly-time') : null),
                "monthlyTime": document.querySelector('[data-monthly-time]') ? document.querySelector('[data-monthly-time]').getAttribute('data-monthly-time') : null,
                "wsUrl": "{{ env('WS_URL') }}",
                "programId": '{{$patientProgramId}}',
                "urlFull": '{{Request::url()}}',
                "urlShort": '{{$urlShort}}',
                "ipAddr": '{{$ipAddr}}',
                "activity": (document.getElementById('activityName') || {value: ''}).value,
                "title": '{{$title}}',
                "submitUrl": '{{route("api.pagetracking")}}',
                "startTime": '{{Carbon\Carbon::now()->subSeconds(8)->toDateTimeString()}}',
                "noLiveCount": ('{{$noLiveCountTimeTracking}}' == '1') ? 1 : 0
            }
        </script>
        @endpush

        @push('scripts')
        <script></script>
        @endpush
    @endif
@endif
