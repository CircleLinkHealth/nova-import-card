@extends('enrollment-ui.layout')

@section('title', 'Enrollment Center')
@section('activity', 'Enrollment Call')

@section('content')

    <?php
    /** @var \CircleLinkHealth\Customer\Entities\User $user */
    $user      = auth()->user();
    $route     = Route::current();
    $routeName = $route->getName();
    //fall back to uri if route name is null
    $title = empty($routeName)
        ? $route->uri
        : $routeName;

    $ipAddr     = Request::ip();
    $urlFull    = Request::url();
    $requestUri = Request::getRequestUri();
    $pieces     = explode('?', $requestUri);
    $urlShort   = $pieces[0];

    $bhiTime     = 0;
    $monthlyTime = $user->formattedTime($ccmTime);
    ?>

    <script>
        window['userId'] = @json($user->id);
        window['userFullName'] = @json($user->getFullName());

        //for time tracker
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

        window['timeTrackerInfo'] = {
            "patientId": '0',
            "providerId": '{{$user->id}}',
            "totalCCMTime": "{{ $ccmTime }}",
            "totalBHITime": "{{ $bhiTime }}",
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
            "wsUrl": "{{ config('services.ws.url') }}",
            "wsUrlFailOver": "{{ config('services.ws.url-fail-over') }}",
            "programId": '{{$user->program_id}}',
            "urlFull": '{{$urlFull}}',
            "urlShort": '{{$urlShort}}',
            "ipAddr": '{{$ipAddr}}',
            "activity": 'CA - Loading next patient',
            "title": '{{$title}}',
            "submitUrl": '{{route("api.pagetracking")}}',
            "timeSyncUrl": undefined,
            "startTime": getCarbonDateTimeStringInServerTimezone(new Date(window.performance.timing.connectStart)),
            "noLiveCount": 0,
            "noCallMode": "{{ config('services.no-call-mode.env') }}",
            "patientFamilyId": "0",
            "isCcm": false,
            "isBehavioral": false,
            "noBhiSwitch": true,

            //new fields for ca panel
            "isFromCaPanel": true,
            "enrolleeId": '0',
        };
    </script>

    <div id="app">
        <enrollment-dashboard
                cpm-caller-url="{{config('services.twilio.cpm-caller-url')}}"
                cpm-token="{{$cpmToken}}"
                :debug="{{json_encode(!isProductionEnv())}}">

        </enrollment-dashboard>
    </div>

    <script src="{{mix('compiled/js/app-enrollment-ui.js')}}"></script>
@stop
