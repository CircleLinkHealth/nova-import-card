@if(isset($class))
    <div class="{{ $class }}">
        @else
            <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                @endif

                <input class="mdl-textfield__input"
                       type="text"
                       id="{{ $name }}"
                       name="{{ $name }}"
                       value="{{ isset($value) ? $value : '' }}">
                <label class="mdl-textfield__label" for="{{ $name }}">{{ $label }}</label>
            </div>