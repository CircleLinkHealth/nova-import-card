@if($errors->has('invalid-browser') || $errors->has('invalid-browser-force-switch'))
    <form method="POST" action="{{ route('store.browser.compatibility.check.preference') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="col-md-12 text-center">
            @if(!$errors->has('invalid-browser-force-switch'))
                <div class="col-md-12">
                    <div class="radio">
                        <input id="doNotShowAgain" type="checkbox" name="doNotShowAgain" value="1">
                        <label for="doNotShowAgain">Don't show this again</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning btn-lg">Continue</button>&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;
            @endif

            <a href="https://www.google.com/chrome/browser/desktop/index.html" class="btn btn-success btn-lg">Download
                Chrome</a>
        </div>

    </form>
@endif