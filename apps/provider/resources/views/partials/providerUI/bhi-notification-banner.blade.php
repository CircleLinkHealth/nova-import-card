<div class="modal-mask">
    <div class="container">
        <div id="legacy-bhi-banner" class="alert alert-light" role="alert">

            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                Close
            </button>

            <div class="row">
                <div class="col-md-12">
                    <div class="alert-heading">
                        <div class="title">
                            <span class="glyphicon glyphicon-exclamation-sign"></span>
                            THIS PATIENT IS ELIGIBLE FOR BEHAVIORAL HEALTH SERVICES, PLEASE ASK:
                        </div>
                    </div>
                    <hr>
                    <div class="message">
                        DR. <strong>{{($user->billingProviderUser())->getFullName()}}</strong> would like us to spend a bit more time on
                        these
                        calls, <br>including covering behavioral and other health topics, and possibly looping in
                        specialists
                        only if needed.
                        <div class="space"></div>
                       <strong> If you don’t have supplemental insurance, there’s an $8 additional copay.</strong>
                        <div class="space" style="margin-top: 5%"></div>
                        <strong>Did patient consent?</strong>
                        <div class="space"></div>
                    </div>
                </div>

                <div class="options col-md-6">
                    {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                    <input type="hidden" name="decision" value="1">
                    <input type="submit"
                           value="Consented"
                           class="alert-success alert with-tooltip"
                           style="margin-left: 25px;"
                           title="Confirm that the patient has consented to receiving BHI services"
                           onclick="return confirm('Please confirm that the patient has consented to receiving BHI services.')">
                    {{ Form::close() }}

                    {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                    <input type="hidden" name="decision" value="2">
                    <input type="submit"
                           value="Not Now"
                           class="alert-warning alert legacy-bhi-consent-not-now-button with-tooltip"
                           style="margin-left: 25px;"
                           title="Hide this notice until this patient's next scheduled call day"
                           onclick="return confirm('Clicking OK will hide the BHI notice until the next day this patient has a scheduled call.')">
                    {{ Form::close() }}

                    {{ Form::open(['url' => route('legacy-bhi.store', [$user->program_id, $user->id]), 'style' => 'display: inline-block;', 'class' => 'legacy-bhi-decision-form']) }}
                    <input type="hidden" name="decision" value="0">
                    <input type="submit" value="Not Consented"
                           class="alert-danger alert with-tooltip"
                           style="margin-left: 25px;"
                           title="Confirm that the patient has NOT consented to receiving BHI services"
                           onclick="return confirm('Please confirm that the patient has NOT consented to receiving BHI services.')">
                    {{ Form::close() }}

                </div>
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

        $(function () {
            $('.close').click(function (e) {
                $(".modal-mask, .load-hidden-bhi").hide();
            });
        });

    </script>
@endpush

@push('styles')
    <style>
        #legacy-bhi-banner {
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 15%;
            top: 14%;
            width: 70%;
            height: 60%;
            overflow: auto;
            border: #000000;
            background-color: whitesmoke;
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
            transition: all .3s ease;
            font-family: Helvetica, Arial, sans-serif;
            color: #000;
        }

        .modal-mask {
            position: fixed;
            z-index: -1;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .5);
            display: table;
            transition: opacity .3s ease;
        }

        .space {
            margin-top: 2%;
        }

        .message {
            font-size: medium;
            line-height: 1.6;
            text-align: center;
        }

        .title {
            font-size: medium;
            letter-spacing: 1px;
            text-align: center;
            font-weight: bold;
        }

        .options {
            position: relative;
            bottom: 5%;
            left: 28%;
        }
    </style>
@endpush