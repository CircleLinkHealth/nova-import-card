<input name="{{$name ?? ''}}" type="radio"
       id="{{$id ?? ''}}"
       value="{{$value}}"
@if(isset($attributes))
    @foreach($attributes as $key => $val)
        {{ $key }}="{{ $val }}"
    @endforeach
@endif/>
<label for="{{$id ?? ''}}">{{$label}}</label>