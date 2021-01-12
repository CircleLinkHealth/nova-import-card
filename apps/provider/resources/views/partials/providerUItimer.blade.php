@if ($enableTimeTracking)
    @push('prescripts')
        <script src="{{asset('/js/date-in-server-timezone.js')}}"></script>
        <script>
            var timeTrackerInfo = {
                "patientId": '{{$patientId}}' === '' ? '0' : '{{$patientId}}',
                "providerId": '{{Auth::user()->id}}',
                "wsUrl": "{{ config('services.ws.url') }}",
                "wsUrlFailOver": "{{ config('services.ws.url-fail-over') }}",
                "programId": '{{ $patientProgramId }}',
                "urlFull": '{{ Request::url() }}',
                "urlShort": '{{ $urlShort }}',
                "ipAddr": '{{ $ipAddr }}',
                "activity": '@yield("activity")',
                "title": '{{ $title }}',
                "submitUrl": '{{route("api.pagetracking")}}',
                "timeSyncUrl": '{{route("api.get.time.patients")}}',
                "startTime": getCarbonDateTimeStringInServerTimezone(new Date(window.performance.timing.connectStart), '{{ config('app.timezone', 'America/New_York') }}'),
                "noLiveCount": ('{{ $noLiveCountTimeTracking }}' == '1') ? 1 : 0,
                "noCallMode": "{{ config('services.no-call-mode.env') }}",
                "patientFamilyId": "{{ $patientFamilyId ?? 0 }}",
                "noBhiSwitch": ('{{ $noBhiSwitch }}' == '1') ? true : false,
                "forceSkip": ('{{ $forceSkip }}' == '1') ? true : false,
                "chargeableServices": @json($chargeableServices),
                "chargeableServiceId": -1
            }
        </script>
    @endpush
@endif
