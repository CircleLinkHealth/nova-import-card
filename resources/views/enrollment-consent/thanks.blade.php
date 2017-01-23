@extends('layouts.enrollment-consent-layout')

@section('content')

    <!-- Square card -->
    <style>
        .demo-card-wide.mdl-card {
            width: 512px;
        }
        .demo-card-wide > .mdl-card__title {
            color: #fff;
            height: 176px;
            background: rgb(79, 178, 226) center / cover;
        }
        .demo-card-wide > .mdl-card__menu {
            color: #fff;
        }

    </style>

    <html>

    <body>
    <div class="centeritems mdl-grid">
        <div class="mdl-cell mdl-cell--3-col mdl-cell--4-col-phone">
            <div class="demo-card-wide mdl-card mdl-shadow--2dp">
                <div class="mdl-card__title">
                    <h1 class="mdl-card__title-text" style="font-size: 45px">Welcome{{$name == null ? '' : ', ' . $name}}!</h1>
                </div>
                <div class="mdl-card__supporting-text" style="font-size: 17px">
                    A personalized care coach will be touch with you shortly.<br />
                </div>
            </div>

        </div>
        <div class="mdl-layout-spacer"></div>
    </div>
    </body>

    </html>

@stop
