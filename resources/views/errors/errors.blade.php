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
            @include('errors.incompatibleBrowser')
        </div>
    @endif
@endif