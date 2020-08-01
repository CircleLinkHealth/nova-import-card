@if (Session::has('messages'))
    @php
        $messages = Session::get('messages') ?? $messages ?? null;
    @endphp
    @if (is_array($messages) && count($messages) > 0)
        <div class="alert alert-success success">
            <ul>
                @foreach ($messages as $key => $message)
                    <li class="{{$key}}">{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @php
        Session::forget('messages');
    @endphp

    @push('styles')
    <style>
        .patient-user {
            font-size: x-large;
            line-height: 30px;
        }
    </style>
    @endpush

@endif

