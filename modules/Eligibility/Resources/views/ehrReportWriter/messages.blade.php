@if (session('errors'))
        <ul class="list-group">
            @foreach (session('errors')->getMessages() as $key => $value)
                @if($key == 'warnings')
                    <div class="alert alert-warning">
                        @foreach ($value as $error)
                            <li class="{{$key}}">
                                {{ $error }}
                            </li>
                        @endforeach
                    </div>
                @endif
                @if($key == 'errors')
                    <div class="alert alert-danger">
                        @foreach ($value as $error)
                            <li class="{{$key}}">
                                {{ $error }}
                            </li>
                        @endforeach
                    </div>
                @endif
                @if($key == 'success')
                    <div class="alert alert-success">
                        @foreach ($value as $error)
                            <li class="{{$key}}">
                                {{ $error }}
                            </li>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </ul>
@endif