<form method="POST" action="{{ url('/auth/login') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    @if($errors->has('invalid-browser') || $errors->has('invalid-browser-force-switch'))
        <div class="col-md-12 text-center">
            @if(!$errors->has('invalid-browser-force-switch'))
                <div class="col-md-12">
                    <div class="radio">
                        <input id="doNotShowAgain" type="checkbox" name="doNotShowAgain">
                        <label for="doNotShowAgain">Don't show this again <span> </span></label>
                    </div>
                </div>

                <a href="{{route('patients.dashboard')}}" class="btn btn-warning btn-lg">Continue</a>&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endif

            <a href="https://www.google.com/chrome/browser/desktop/index.html" class="btn btn-success btn-lg">Download
                Chrome</a>
        </div>
    @endif
</form>
