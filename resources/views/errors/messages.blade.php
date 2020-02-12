@if (Session::has('messages'))
    @php
    $messages = Session::get('messages') ?? $messages ?? null;
    @endphp
    @if (is_array($messages) && count($messages) > 0)
        <div class="alert alert-success success">
            <ul>
                @foreach ($messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @php
        Session::forget('messages');
    @endphp
@endif

