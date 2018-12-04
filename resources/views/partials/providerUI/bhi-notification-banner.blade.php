<div id="legacy-bhi-banner" class="alert alert-warning alert-dismissible notification-banner" role="alert"
     style="background-color: #fefac0;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>

                <span class="banner-title">THIS PATIENT IS ELIGIBLE FOR BEHAVIORAL HEALTH SERVICES, PLEASE ASK: </span>
                <p class="banner-body">
                    "DR. {{($user->billingProviderUser())->getFullName()}} would like us to spend a bit more time on these
                    calls, including covering behavioral and other health topics, and possibly looping in specialists
                    only if needed. If you don’t have supplemental insurance, there’s an $8 additional copay".

                    <br>
                    <br>

                    Did patient consent?

                    <br>
                    <br>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                <input type="hidden" name="decision" value="1">
                <input type="submit" value="Consented" class="alert-success alert"
                       style="margin-left: 25px;"
                       onclick="return confirm('Please confirm that the patient has consented to receiving BHI services.')">
                {{ Form::close() }}

                {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                <input type="hidden" name="decision" value="2">
                <input type="submit" value="Not Now" class="alert-warning alert legacy-bhi-consent-not-now-button"
                       style="margin-left: 25px;"
                       onclick="return confirm('Clicking OK will hide the BHI notice until the next day this patient has a scheduled call.')">
                {{ Form::close() }}

                {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                <input type="hidden" name="decision" value="0">
                <input type="submit" value="Not Consented" class="alert-danger alert"
                       style="margin-left: 25px;"
                       onclick="return confirm('Please confirm that the patient has NOT consented to receiving BHI services.')">
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).on("click", '#close-legacy-bhi-banner', function (event) {
            event.preventDefault();
            $('#legacy-bhi-banner').hide();
        });
    </script>
@endpush