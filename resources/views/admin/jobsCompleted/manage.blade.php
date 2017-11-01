@extends('partials.adminUI')

@section('content')
    <div class="container">
        @foreach(auth()->user()->cachedViews() as $cache)
            @if(!$cache['view'])
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong>{{$cache['message']}}
                </div>

            @else
                <div class="alert alert-success" role="alert">
                    {{$cache['message']}}
                    <a href="{{route('get.cached.vue.by.key', ['key' => $cache['key']])}}" class="alert-link">Go to page</a>
                    <p class="text-right">Requested at {{$cache['created_at']}}</p>
                    <p class="text-right">Expires at {{$cache['expires_at']}}</p>
                </div>
            @endif
        @endforeach
    </div>
@endsection