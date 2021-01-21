@extends(config("laravel-feature-flag.default_view")) @section('feature-toggle-content')

@can('feature-flag', 'testing')
    Testing On
@endcan

@cannot('feature-flag', 'testing')
    Testing Off
@endcan


@endsection