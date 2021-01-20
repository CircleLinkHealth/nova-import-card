<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .card-data {
                display: inherit;
            }
        }
    </style>
</head>

<?php

$style = [
    // Layout ------------------------------

    'body'          => 'margin: 0; padding: 0; width: 100%; background-color: #F2F4F6;',
    'email-wrapper' => 'width: 100%; margin: 0; padding: 0; background-color: #F2F4F6;',

    // Masthead -----------------------

    'email-masthead'      => 'padding: 25px 0; text-align: center;',
    'email-masthead_name' => 'font-size: 16px; font-weight: bold; color: #2F3133; text-decoration: none; text-shadow: 0 1px 0 white;',

    'email-body'       => 'width: 100%; margin: 0; padding: 0; border-top: 1px solid #EDEFF2; border-bottom: 1px;padding-left: 6px; padding-right: 6px; solid #EDEFF2; background-color: #FFF;',
    'email-body_inner' => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0;',
    'email-body_cell'  => ' padding-top: 15px; padding-bottom: 60px;',
    // Card ----------------------------------
    'card'            => 'border:1px #edeff2 solid; border-radius:10px; height:155px; width: 100%; margin: 0 auto; padding: 0;',
    'circular-square' => 'width: 75px; height: 75px; border-radius: 50%;',
    'card-data'       => 'display: inherit; margin-right: 40em;',
    'card-name'       => 'height: 6px; font-size: 16px; color: #282828;',
    'card-date'       => 'font-size: 16px; color: #a9a9a9;',

    'email-footer'      => 'width: auto; max-width: 570px; margin: 0 auto; padding: 0; text-align: center;',
    'email-footer_cell' => 'color: #AEAEAE; padding: 35px; text-align: center;',
    // Logo ----------------------------------
    'logo' => 'width: 197px;',

    // Body ------------------------------

    'body_action' => 'width: 100%; margin: 30px auto; padding: 0; text-align: center;',
    'body_sub'    => 'margin-top: 25px; padding-top: 25px; border-top: 1px solid #EDEFF2;',

    // Type ------------------------------

    'anchor'           => 'color: #3869D4;',
    'header-1'         => 'margin-top: 0; color: #2F3133; font-size: 19px; font-weight: bold; text-align: left;',
    'paragraph'        => 'margin-top: 0; color: #74787E; font-size: 17px; line-height: 1.5em;',
    'paragraph-sub'    => 'margin-top: 0; color: #a9a9a9bf; font-size: 12px; line-height: 1.5em;',
    'paragraph-center' => 'text-align: center;',

    // Buttons ------------------------------

    'button' => 'display: block; display: inline-block; width: 200px; min-height: 20px; padding: 10px;
                 background-color: #3869D4; color: #ffffff; font-size: 15px; line-height: 25px;
                 text-align: center; text-decoration: none; -webkit-text-size-adjust: none; border-radius: 8px;',

    'button-alignment' => 'float: left;',

    'button--green'     => 'background-color: #22BC66;',
    'button--red'       => 'background-color: #dc4d2f;',
    'button--blueLight' => 'background-color: #2bbce3;',
];
?>
{{--Montserrat Regular--}}{{-- is what Christian sent me. But doesnt look like the mockup --}}
<?php $fontFamily = 'font-family: sans-serif; font-weight: 600; letter-spacing: 0.5px;'; ?>

<body style="{{ $style['body'] }}">
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="{{ $style['email-wrapper'] }}" align="center">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="{{ $style['email-body'] }}" width="100%">
                        <table style="{{ $style['email-body_inner'] }}" width="570" cellpadding="0"
                               cellspacing="0">

                            <!-- Logo -->
                            <tr>
                              @if(! isset($excludeLogo))
                                    <td>
                                        <img src="{{asset('img/logos/LogoHorizontal_Color.svg')}}"
                                             alt="{{config('app.name')}}"
                                             style="{{ $style['logo'] }}">
                                        <hr style="border-color: #edeff247">
                                    </td>
                                @endif
                            </tr>

                            <!-- Email Body -->
                            <tr>
                                <td style="{{ $fontFamily }} {{ $style['email-body_cell'] }}">
                                    <!-- Greeting -->
                                    <h1 style="{{ $style['header-1'] }}">
                                        @if (! empty($greeting))
                                            {{ $greeting }}
                                        @else
                                            @if ($level == 'error')
                                                Whoops!
                                                {{--                                            @else--}}
                                                {{--                                                Hello!--}}
                                            @endif
                                        @endif
                                    </h1>

                                    <!-- Intro -->
                                    @foreach ($introLines as $line)
                                        <p style="{{ $style['paragraph'] }}">
                                            {!!  $line  !!}
                                        </p>
                                    @endforeach

                                <!-- New Card with sender's info -->
                                    @if(isset($emailData))
                                        <table style="{{ $style['card'] }}">
                                            <tr>
                                                <td style="padding-left: 32px;">
                                                    <img style="{{$style['circular-square']}}"
                                                         alt="Profile image"
                                                         src="https://cdn2.iconfinder.com/data/icons/solid-glyphs-volume-2/256/user-unisex-512.png"/>
                                                </td>
                                                <td style="{{$style['card-data']}}">
                                                    {{--@todo: pass $var_name here--}}
                                                    <h4 style="{{$style['card-name']}}">{{$emailData['senderName']}}</h4>
                                                    <h4 style="{{$style['card-date']}}">{{$emailData['date']}}</h4>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif

                                <!-- Action Button -->
                                    @if (isset($actionText))
                                        <table style="{{ $style['body_action'] }}" align="center"
                                               width="100%"
                                               cellpadding="0" cellspacing="0">
                                            <tr style="{{$style['button-alignment']}}">
                                                <td align="center">
                                                    <?php
                                                    switch ($level) {
                                                        case 'success':
                                                            $actionColor = 'button--green';
                                                            break;
                                                        case 'error':
                                                            $actionColor = 'button--red';
                                                            break;
                                                        default:
                                                            $actionColor = 'button--blueLight';
                                                    }
                                                    ?>

                                                    <a href="{{ $actionUrl }}"
                                                       style="{{ $fontFamily }} {{ $style['button'] }} {{ $style[$actionColor] }}"
                                                       class="button"
                                                       target="_blank">
                                                        {{ $actionText }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif

                                <!-- Outro -->
                                    @foreach ($outroLines as $line)
                                        <p style="{{ $style['paragraph'] }}">
                                            {!! $line !!}
                                        </p>
                                    @endforeach

                                <!-- Salutation -->
                                    <p style="{{ $style['paragraph'] }}">

                                    </p>

                                    <!-- Sub Copy -->
                                    @if (isset($actionText))
                                        <table style="{{ $style['body_sub'] }}">
                                            <tr>
                                                <td style="font-family: sans-serif; letter-spacing: 0.5px;">
                                                    @if(isset($emailData))
                                                        <p style="{{ $style['paragraph-sub'] }}">
                                                            This message was sent to <a
                                                                    style="color: #376a9c">{{$emailData['notifiableMail']}}
                                                                .
                                                            </a>
                                                            If you don't want to receive these emails from
                                                            {{--For Patients--}}
                                                            @if(isset($practiceName) && ! empty($practiceName))
                                                                {{$practiceName}}<br>
                                                            @else
                                                                CircleLink
                                                                <br>Health
                                                            @endif
                                                            in the future, please <a href="{{$url}}"
                                                                                     style="color: #376a9c">unsubscribe.</a>
                                                        </p>

                                                        <p style="{{ $style['paragraph-sub'] }}">
                                                            If you’re having trouble clicking the "{{ $actionText }}"
                                                            button, copy and paste the URL below into your <br> web
                                                            browser:
                                                        </p>
                                                    @else
                                                        <p style="{{ $style['paragraph-sub'] }}">
                                                            If you’re having trouble clicking the "{{ $actionText }}"
                                                            button, copy and paste the URL below into your <br> web
                                                            browser:
                                                        </p>
                                                    @endif
                                                    <p style="{{ $style['paragraph-sub'] }}">
                                                        <a style="{{ $style['anchor'] }}" href="{{ $actionUrl }}"
                                                           target="_blank">
                                                            {{ $actionUrl }}
                                                        </a>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td>
                        <table style="{{ $style['email-footer'] }}" align="center" cellpadding="0"
                               cellspacing="0">
                            <tr>
                                <td style="{{ $fontFamily }} {{ $style['email-footer_cell'] }}">
                                    <p style="{{ $style['paragraph-sub'] }}">
                                        {{--For Patients--}}
                                        @if(isset($practiceName) && ! empty($practiceName))
                                            <span style="{{ $style['anchor'] }}">{{$practiceName}}</span>
                                            @else
                                            &copy; {{ date('Y') }}
                                            <a style="{{ $style['anchor'] }}" href="{{ url('/') }}" target="_blank">CircleLink
                                                Health</a>.
                                            All rights reserved.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
