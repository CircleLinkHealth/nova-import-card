@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Rule Matches</div>
                    <strong>Params:</strong>
                    <ul>
                    @foreach( $params as $key => $value )
                            <li><strong>{{ $key  }}</strong> -- {{ $value }}</li>
                    @endforeach
                    </ul>

                    <strong>Rule Matches:</strong>
                    @if (!empty($ruleActions))
                        <ul>
                            @foreach( $ruleActions as $rule )
                                <li>{{ $rule->id }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No rule matches found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
