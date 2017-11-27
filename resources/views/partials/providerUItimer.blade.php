<input type="hidden" name="activityName" id="activityName" value="@yield('activity')">

@push('prescripts')
    <?php 
        if (isset($patient)) {
            $patientId = $patient->id;
        }
    ?>
    <script>

        var timeTrackerInfo = {
            "patientId": '{{$patientId}}' === '' ? '0' : '{{$patientId}}',
            "providerId": '{{Auth::user()->id}}',
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
            "programId": '{{$patientProgramId}}',
            "urlFull": '{{Request::url()}}',
            "urlShort": '{{$urlShort}}',
            "ipAddr": '{{$ipAddr}}',
            "activity": (document.getElementById('activityName') || { value: '' }).value,
            "title": '{{$title}}',
            "submitUrl": '{{route("api.pagetracking")}}',
            "startTime": '{{Carbon\Carbon::now()->subSeconds(8)->toDateTimeString()}}',
            "noLiveCount": ('noLiveCountTimeTracking' == 'true') ? 1 : 0
        }
    </script>
@endpush

@if ($enableTimeTracking)
    @push('scripts')
        <script></script>
    @endpush
@endif