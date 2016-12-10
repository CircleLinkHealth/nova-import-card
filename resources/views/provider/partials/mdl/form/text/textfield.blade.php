@if(isset($class))
    <div class="input-field {{ $class }}">
        @else
            <div class="input-field">
                @endif

                <input class="validate"
                       type="{{ isset($type) ? $type : 'text' }}"
                       id="{{ $name }}"
                       name="{{ $name }}"
                       value="{{ isset($value) ? $value : old($name)}}"
                @if(isset($attributes))
                    @foreach($attributes as $key => $val)
                        {{ $key }}="{{ $val }}"
                    @endforeach
                @endif>
                <label for="{{ $name }}" data-error="Invalid input." data-success="">{{ $label }}</label>
            </div>






