@if (isset($errors))
    @if (count($errors) > 0)
        <div class="red lighten-5 red-text text-darken-5" style="padding: 4% 5% 1% 5%;">
            <strong>We have some concerns regarding with your input.</strong><br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="padding-bottom: 2%;">{!! $error  !!} </li>
                @endforeach
            </ul>
        </div>
    @endif
@endif