<input name="{{$name}}" type="checkbox"
       id="{{$id ?? $name}}"
       value="{{$value}}"
@if(isset($attributes))
    @foreach($attributes as $key => $val)
        {{ $key }}="{{ $val }}"
    @endforeach
@endif/>
<label for="{{$id ?? $name}}">{{$label}}</label>