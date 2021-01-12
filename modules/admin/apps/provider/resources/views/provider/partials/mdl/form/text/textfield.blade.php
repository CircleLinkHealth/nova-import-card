@if(isset($class))
    <div class="input-field {{ $class }}">
        @else
            <div class="input-field">
                @endif

                <input class="validate"
                       type="{{ $type ?? 'text' }}"
                       id="{{ $id ?? '' }}"
                       name="{{ $name ?? '' }}"
                       value="{{ $value ?? old($name ?? '')}}"
                @if(isset($attributes))
                    @foreach($attributes as $key => $val)
                        {{ $key }}="{{ $val }}"
                    @endforeach
                @endif>
                <label class="{{$label_class ?? ''}}" for="{{ $name ?? null }}"
                       data-error="{{ $data_error ?? 'Invalid input.' }}"
                       data-success="{{ $data_success ?? '' }}">{{ $label }}</label>
            </div>






