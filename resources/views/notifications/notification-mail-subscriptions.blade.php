@extends('partials.providerUI')
@section('title', 'Subscribe Notification')
@section('activity', 'Subscribe Notification')
@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Email Subscriptions</h2>
            </div>
            <div class="card-body">
                <div class="subscriptions" style="display: inline-flex;">
                    <ul class="subscribes">
                        @foreach($subscriptions as $array)
                            @foreach($array as $status => $subscriptionType){{-- $status = checked or empty --}}
                            {!! Form::open(['url' => route('update.subscriptions'), 'method' => 'post']) !!}
                            <div class="checkbox">
                                <label>
                                    <input
                                            type="checkbox"
                                            name="subscriptionTypes[]"
                                            value="{{$subscriptionType}}" {{$status}}>
                                    {{\Illuminate\Support\Str::plural($subscriptionType)}}
                                </label>

                            </div>

                            @endforeach
                        @endforeach

                        <div class="form-group">
                            <div class="col-md-2 control-label">
                                <button id="submit" type="submit"
                                        class="btn btn-success">
                                    Submit
                                </button>
                            </div>
                        </div>

                        {!! Form::close() !!}

                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection()

<style>
    .card-header {
        padding-left: 40px;
    }

    #app > div.container > div > div.card-body > div > ul.subscribes > form > div > label > input[type=checkbox] {
        display: inline-block;
    }
</style>
