@if ($enableTimeTracking)
    @push('prescripts')
        @php
            if (isset($patient)) {
                $patientId = $patient->id;

                $patientFamilyId = null;

                $patientIsCcm        = false;
                $patientIsBehavioral = false;

                if ($patient instanceof \CircleLinkHealth\Customer\Entities\Patient) {
                    $user            = optional($patient->user);
                    $patientId       = $user->id;
                    $patientFamilyId = $patient->family_id;

                    $patientIsCcm        = $user->isCcm();
                    $patientIsBehavioral = $user->isBhi();
                } elseif ($patient instanceof \CircleLinkHealth\Customer\Entities\User) {
                    $patientFamilyId     = optional($patient->patientInfo)->family_id;
                    $patientIsCcm        = $patient->isCcm();
                    $patientIsBehavioral = $patient->isBhi();
                }
            } else {
                $patientIsCcm        = false;
                $patientIsBehavioral = false;
            }
            if (auth()->guest()) {
                throw new \Exception('You are not logged in - Michalis is curious to see if this will ever be triggered.');
            }

            $noLiveCountTimeTracking = (isset($noLiveCountTimeTracking) && $noLiveCountTimeTracking);
            if ( ! $noLiveCountTimeTracking) {
                $noLiveCountTimeTracking = auth()->user()->isCCMCountable();
            }
        @endphp
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

            var activityName = '@yield("activity")';

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
                "wsUrlFailOver": "{{ config('services.ws.url-fail-over') }}",
                "programId": '{{$patientProgramId}}',
                "urlFull": '{{Request::url()}}',
                "urlShort": '{{$urlShort}}',
                "ipAddr": '{{$ipAddr}}',
                "activity": activityName,
                "title": '{{$title}}',
                "submitUrl": '{{route("api.pagetracking")}}',
                "timeSyncUrl": '{{route("api.get.time.patients")}}',
                "startTime": getCarbonDateTimeStringInServerTimezone(new Date(window.performance.timing.connectStart)),
                "noLiveCount": ('{{$noLiveCountTimeTracking}}' == '1') ? 1 : 0,
                "noCallMode": "{{ config('services.no-call-mode.env') }}",
                "patientFamilyId": "{{ $patientFamilyId ?? 0 }}",
                "isCcm": ('{{ $patientIsCcm }}' == '1') ? true : false,
                "isBehavioral": ('{{ $patientIsBehavioral }}' == '1') ? true : false,
                "noBhiSwitch": ('{{ $noBhiSwitch }}' == '1') ? true : false,
                "forceSkip": ('{{ $forceSkip }}' == '1') ? true : false
            }
        </script>
    @endpush
@endif
