<time-tracker ref="TimeTrackerApp"
              class-name="{{$noLiveCountTimeTracking ? 'color-grey' : ''}}"
              :twilio-enabled="@json(config('services.twilio.enabled') && (isset($patient) && $patient->primaryPractice ? $patient->primaryPractice->isTwilioEnabled() : true))"
              :info="timeTrackerInfo"
              route-activities="{{ empty($patient->id) ?: route('patient.activity.providerUIIndex', [$patient->id]) }}"
              :disable-time-tracking="@json(isset($disableTimeTracking) && $disableTimeTracking)"
              :no-live-count="@json($noLiveCountTimeTracking ? true : false)"
              :override-timeout="{{config('services.time-tracker.override-timeout')}}">
    @include('partials.tt-loader')
</time-tracker>
