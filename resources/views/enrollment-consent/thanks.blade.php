@extends('layouts.enrollment-consent-layout')

@section('content')

    <!-- Square card -->
    <style>
        .demo-card-wide.mdl-card {
            width: 100%;
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

    <main class="mdl-layout__content">
        <div class="mdl-card" style="width: 100%; align-items: center">
        <div class="mdl-cell" >
            <div class="demo-card-wide mdl-card mdl-shadow--2dp">
                <div class="mdl-card__title">
                    <h1 class="mdl-card__title-text" style="font-size: 45px">Welcome{{$name == null ? '' : ', ' . $name}}!</h1>
                </div>
                <div class="mdl-card__supporting-text" style="font-size: 17px">
                    A personal care coach will be in touch shortly.<br />
                </div>
            </div>

        </div>
    </div>
    </main>

    </html>

@stop
