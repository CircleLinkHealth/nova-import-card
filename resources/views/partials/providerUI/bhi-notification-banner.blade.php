<div id="legacy-bhi-banner" class="alert alert-warning alert-dismissible notification-banner" role="alert"
     style="background-color: #fefac0;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>

                <span class="banner-title">THIS PATIENT IS ELIGIBLE FOR BEHAVIORAL HEALTH SERVICES, PLEASE ASK: </span>
                <p class="banner-body">
                    "DR. {{($user->billingProviderUser())->full_name}} would like us to also cover behavioral health
                    topics on
                    these calls, which may include looping in other services, like behavioral health specialists.
                    Finally, if
                    you don't have supplemental insurance, there is a $8-16 copay".

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

                <button id="close-legacy-bhi-banner"
                        class="alert alert-warning legacy-bhi-consent-not-now-button close"
                        style="display: inline-block;">
                    Not Now
                </button>

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