<input name="{{$name}}" type="radio"
       id="{{$id ?? $name}}"
       value="{{$value}}"
@if(isset($attributes))
    @foreach($attributes as $key => $val)
        {{ $key }}="{{ $val }}"
    @endforeach
@endif/>
<label for="{{$id ?? $name}}">{{$label}}</label>