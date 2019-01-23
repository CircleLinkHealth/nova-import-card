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

            $patientFamilyId = null;

            $patientIsCcm        = false;
            $patientIsBehavioral = false;

            if (is_a($patient, Patient::class)) {
                $user            = optional($patient->user()->first());
                $patientId       = $user->id;
                $patientFamilyId = $patient->family_id;

                $patientIsCcm        = $user->isCcm();
                $patientIsBehavioral = $user->isBhi();
            } else {
                $patientFamilyId     = optional($patient->patientInfo()->first())->family_id;
                $patientIsCcm        = $patient->isCcm();
                $patientIsBehavioral = $patient->isBhi();
            }
        } else {
            $patientIsCcm        = false;
            $patientIsBehavioral = false;
        }
        $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
        ?>
        <script>

            var timeTrackerInfo = {
                "patientId": '{{$patientId}}' === '' ? '0' : '{{$patientId}}',
                "providerId": '{{Auth::user()->id}}',
                "totalCCMTime": "{{ $ccm_time }}",
                "totalBHITime": "{{ $bhi_time }}",
                "totalTime": (function (monthlyTime) {
                                if (monthlyTime) {
                                    var split = monthlyTime.split(':');
                                    var seconds = Number(split[2]), minutes = Number(split[1]), hours = Number(split[0]);
                                    return seconds +
                                            (minutes * 60) +
                                            (hours * 60 * 60);
                                }
                                return 0;
                            })('{{$monthlyTime}}'),
                "monthlyTime": '{{$monthlyTime}}',
                "monthlyBhiTime": '{{$monthlyBhiTime}}',
                "wsUrl": "{{ config('services.ws.url') }}",
                "programId": '{{$patientProgramId}}',
                "urlFull": '{{Request::url()}}',
                "urlShort": '{{$urlShort}}',
                "ipAddr": '{{$ipAddr}}',
                "activity": (document.getElementById('activityName') || {value: ''}).value,
                "title": '{{$title}}',
                "submitUrl": '{{route("api.pagetracking")}}',
                "timeSyncUrl": '{{route("api.get.time.patients")}}',
                "startTime": '{{Carbon\Carbon::now()->subSeconds(8)->toDateTimeString()}}',
                "noLiveCount": ('{{$noLiveCountTimeTracking}}' == '1') ? 1 : 0,
                "noCallMode": "{{ config('services.no-call-mode.env') }}",
                "patientFamilyId": "{{ $patientFamilyId ?? 0 }}",
                "isCcm": ('{{ $patientIsCcm }}' == '1') ? true : false,
                "isBehavioral": ('{{ $patientIsBehavioral }}' == '1') ? true : false,
                "noBhiSwitch": ('{{ $noBhiSwitch }}' == '1') ? true : false
            }
        </script>
        @endpush

        @push('scripts')
        <script></script>
        @endpush
    @endif
@endif
