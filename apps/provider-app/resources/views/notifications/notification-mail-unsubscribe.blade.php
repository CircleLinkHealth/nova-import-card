@extends('partials.providerUI')
@section('title', 'Unsubscribe Notification')
@section('activity', 'Unsubscribe Notification')
@section('content')

    <div class="container">
        <div class="message">
            <h2>
                <br>
                You have unsubscribed from receiving <span
                        class="badge badge-primary">{{\Illuminate\Support\Str::plural($activityType)}}</span> notifications.
                <br>
                To manage your notification subscriptions <a href="{{route('subscriptions.notification.mail')}}" style="color: #2e92cc">click
                    here</a>
                <br>
                <i class="glyphicon glyphicon-envelope"></i>
            </h2>
        </div>
    </div>
@endsection

<style>
    .message {
        text-align: center;
        padding-top: 300px;
    }

    #app > div.container > div > h2 > span {
        font-size: 20px;
        background-color: #2e92cc;
    }

    #app > div.container > div > h2 > i {
        color: #2e92cc;
        font-size: 65px;
    }
</style>
