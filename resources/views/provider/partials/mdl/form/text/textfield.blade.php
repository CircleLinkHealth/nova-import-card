@if(isset($class))
    <div class="mdl-textfield mdl-js-textfield {{ $class }}">
        @else
            <div class="mdl-textfield mdl-js-textfield">
                @endif

                <input class="mdl-textfield__input"
                       type="{{ isset($type) ? $type : 'text' }}"
                       id="{{ $name }}"
                       name="{{ $name }}"
                       value="{{ isset($value) ? $value : '' }}">
                <label class="mdl-textfield__label" for="{{ $name }}">{{ $label }}</label>
            </div>