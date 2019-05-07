<?php
use CircleLinkHealth\Customer\Entities\Patient;

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
            $ccmCountableUser        = auth()->user()->isCCMCountable();
            $noLiveCountTimeTracking = isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking;
            if ( ! $noLiveCountTimeTracking) {
                $noLiveCountTimeTracking = $ccmCountableUser
                    ? 0
                    : 1;
            }
            ?>
            <script>

                function addPadding(v) {
                    return v.toString().length < 2 ? `0${v}` : v;
                }

                //four digit year
                function getYear(d) {
                    return d.getFullYear();
                }

                //getMonth: 0 - 11
                function getMonth(d) {
                    return addPadding(d.getMonth() + 1);
                }

                //getDate: 1 - 31
                function getDate(d) {
                    return addPadding(d.getDate());
                }

                //getHours: 0 - 23
                function getHours(d) {
                    return addPadding(d.getHours());
                }

                //getMinutes: 0 - 59
                function getMinutes(d) {
                    return addPadding(d.getMinutes());
                }

                //getSeconds: 0 - 59
                function getSeconds(d) {
                    return addPadding(d.getSeconds());
                }

                function getTime(d) {
                    return `${getHours(d)}:${getMinutes(d)}:${getSeconds(d)}`;
                }

                /**
                 * Take a date object in javascript,
                 * convert to server timezone,
                 * and return a string that matches Carbon::toDateTimeString()
                 *
                 * @param date
                 * @returns {string}
                 */
                function getCarbonDateTimeStringInServerTimezone(date) {
                    const serverTimezone = '{{config('app.timezone', 'America/New_York')}}';
                    //1.take local time and make sure it matches the server timezon
                    const serverDateTimeStr = date.toLocaleString("en-US", {timeZone: serverTimezone});
                    //2.create a new date time object from the string produced
                    const serverDateTimeObj = new Date(serverDateTimeStr);
                    //3.format this date time obj to match Carbon::toDateTimeString()
                    return `${getYear(serverDateTimeObj)}-${getMonth(serverDateTimeObj)}-${getDate(serverDateTimeObj)} ${getTime(serverDateTimeObj)}`;
                }

                var timeTrackerInfo = {
                    "patientId": '{{$patientId}}' === '' ? '0' : '{{$patientId}}',
                    "providerId": '{{Auth::user()->id}}',
                    "totalCCMTime": "{{ $ccm_time }}",
                    "totalBHITime": "{{ $bhi_time }}",
                    //totalTime is wrong: hopefully, its not used on time tracker
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
                    {{--                    "startTimeOld": '{{Carbon\Carbon::now()->subSeconds(8)->toDateTimeString()}}', //what's the point of this?--}}
                    "startTime": getCarbonDateTimeStringInServerTimezone(new Date(window.performance.timing.connectStart)),
                    "noLiveCount": ('{{$noLiveCountTimeTracking}}' == '1') ? 1 : 0,
                    "noCallMode": "{{ config('services.no-call-mode.env') }}",
                    "patientFamilyId": "{{ $patientFamilyId ?? 0 }}",
                    "isCcm": ('{{ $patientIsCcm }}' == '1') ? true : false,
                    "isBehavioral": ('{{ $patientIsBehavioral }}' == '1') ? true : false,
                    "noBhiSwitch": ('{{ $noBhiSwitch }}' == '1') ? true : false
                }
            </script>
        @endpush
    @endif
@endif
