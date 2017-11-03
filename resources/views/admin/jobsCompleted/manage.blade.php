@extends('partials.adminUI')

@push('styles')
<style>
    .job-completed-card {
        background-color: #fafafa;
        color: #212121;
        padding: 3rem;
        border-radius: 2rem;
        font-family: 'Roboto', arial, 'Noto Sans Japanese', sans-serif;
        margin: 4rem;
    }

    .job-completed-card-title {
        color: #ff5723;
        border-bottom: 1px solid #e6e6e6;
        padding: 1rem;
    }

    .job-completed-card-footer {
        font-size: 12px;
        margin: 40px 0 0 0;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @foreach(auth()->user()->cachedViews() as $cache)
                    @if(!$cache['key'])
                        <div class="job-completed-card col-md-12 text-center">
                            <h5>{{$cache['title'] ?? ''}}</h5>

                            <p>{!! $cache['description'] ?? '' !!}</p>

                            <p class="job-completed-card-footer">
                                <span class="pull-left">created: <strong>{{$cache['created_at']}}</strong></span>
                                <span class="pull-right">expires: <strong>{{$cache['expires_at']}}</strong></span>
                            </p>
                        </div>
                    @else
                        <div class="job-completed-card col-md-12 text-center">
                            <h3 class="job-completed-card-title">
                                {{$cache['title'] ?? ''}}
                            </h3>
                            <a href="{{route('get.cached.view.by.key', ['key' => $cache['key']])}}" class="alert-link">Go to
                                page</a>

                            <h5>{{$cache['description'] ?? ''}}</h5>

                            <p class="job-completed-card-footer">
                                <span class="pull-left">created: <strong>{{$cache['created_at']}}</strong></span>
                                <span class="pull-right">expires: <strong>{{$cache['expires_at']}}</strong></span>
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection