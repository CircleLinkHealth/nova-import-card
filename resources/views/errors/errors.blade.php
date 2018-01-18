@if (isset($errors))
    @if (count($errors) > 0)
        <div class="alert alert-danger" style="line-height: 2">
            <ul class="list-group">
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>

        <div class="row" style="margin-bottom: 5%;">
            @if($errors->has('invalid-browser') || $errors->has('invalid-browser-force-switch'))
                <div class="col-md-12 text-center">
                    @if(!$errors->has('invalid-browser-force-switch'))
                        <a href="{{route('patients.dashboard')}}" class="btn btn-warning btn-lg">Continue</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    @endif

                    <a href="https://www.google.com/chrome/browser/desktop/index.html" class="btn btn-success btn-lg">Download
                        Chrome</a>
                </div>
            @endif
        </div>
    @endif
@endif