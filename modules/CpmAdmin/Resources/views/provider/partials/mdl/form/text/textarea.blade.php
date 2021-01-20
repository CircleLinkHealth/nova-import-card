@if(isset($class))
    <div class="mdl-textfield mdl-js-textfield {{ $class }}">
        @else
            <div class="mdl-textfield mdl-js-textfield">
                @endif

                <textarea
                        class="mdl-textfield__input"
                        id="{{ $name }}-textarea"
                        name="{{ $name }}"
                        type="text"
                >{{ isset($value) ? $value : '' }}</textarea>
                <label class="mdl-textfield__label" for="{{ $name }}-textarea">{{ $label }}</label>
            </div>