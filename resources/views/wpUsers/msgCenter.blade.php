@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <style id="full-width-slider">
        /* Hide the number input */
        .full-width-slider input {
            display: none;
        }
        .full-width-slider .ui-slider-track {
            margin-left: 15px;
        }
    </style>
    <script id="dynamic-slider">
        $( document ).on( "pagecreate", function() {
            $( "<input type='number' data-type='range' min='0' max='100' step='1' value='17'>" )
                    .appendTo( "#dynamic-slider-form" )
                    .slider()
                    .textinput()
        });
    </script>

    <style type="text/css">
        @-webkit-keyframes fade-out {
            0% { opacity: 1; }
            100% { opacity: 0;}
        }

        #alert-msg {
            display:inline-block;
            float:left;
            border:1px solid #060;
            background:#FFC;
            padding:10px 20px;
            box-shadow:2px 2px 4px #666;
            color:Navy;
            font-weight:bold;
            /*display:none;*/
        }


        #alert-msgS {
            -webkit-animation: fade-out 10s ease-in;
            -webkit-animation-fill-mode: forwards;
            -webkit-animation-iteration-count: 1;
        }
    </style>


    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        User ID: {{ $wpUser->id }}
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <h2>App Simulator:</h2>
                        @if (count($cpFeed['CP_Feed']) == 0)
                            No feed data to show?
                        @else
                            @foreach( $cpFeed['CP_Feed'] as $key => $value )
                                <div class="row col-lg-12" style="border:3px solid #286090;margin:20px 0px;">
                                    <button class="btn btn-primary" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}" @if ($activeDate != $cpFeed['CP_Feed'][$key]['Feed']['FeedDate']) aria-expanded="false" @endif aria-controls="collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}">
                                        {{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}
                                    </button>
                                    <div @if ($activeDate != $cpFeed['CP_Feed'][$key]['Feed']['FeedDate']) class="collapse" @endif id="collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}">
                                    @foreach( $cpFeedSections as $section )
                                        <button class="btn btn-orange" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#collapse{{ $key.'-'.$section }}" aria-expanded="false" aria-controls="collapse{{ $key.'-'.$section }}">
                                            {{ $section }}
                                        </button>
                                        <div class="row">
                                        <div class="col-lg-12 collapse" id="collapse{{ $key.'-'.$section }}" style="background:#fffdf4;border:1px solid #5db3e1;">
                                           <h3>{{ $section }}</h3>

                                        @foreach( $cpFeed['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio )
                                            {!! $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['formHtml'] !!}
                                            @if (isset($arrBio['Response'][0]))
                                                {!! $arrBio['Response'][0]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][0]['Response'][0]))
                                                {!! $arrBio['Response'][0]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][1]))
                                                {!! $arrBio['Response'][1]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][1]['Response'][0]))
                                                {!! $arrBio['Response'][1]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][2]))
                                                {!! $arrBio['Response'][2]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][2]['Response'][0]))
                                                {!! $arrBio['Response'][2]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][3]))
                                                {!! $arrBio['Response'][3]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][3]['Response'][0]))
                                                {!! $arrBio['Response'][3]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][4]))
                                                {!! $arrBio['Response'][4]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][4]['Response'][0]))
                                                {!! $arrBio['Response'][4]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][5]))
                                                {!! $arrBio['Response'][5]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][5]['Response'][0]))
                                                {!! $arrBio['Response'][5]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][6]))
                                                {!! $arrBio['Response'][6]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][6]['Response'][0]))
                                                {!! $arrBio['Response'][6]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][7]))
                                                {!! $arrBio['Response'][7]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][7]['Response'][0]))
                                                {!! $arrBio['Response'][7]['Response'][0]['formHtml'] !!}
                                            @endif

                                            @if (isset($arrBio['Response'][8]))
                                                {!! $arrBio['Response'][8]['formHtml'] !!}
                                            @endif
                                            @if (isset($arrBio['Response'][8]['Response'][0]))
                                                {!! $arrBio['Response'][8]['Response'][0]['formHtml'] !!}
                                            @endif
                                        @endforeach

                                        </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <h2>Recent Action:</h2>
                        @if (count($comments) == 0)
                            No recent action to show
                        @else
                            @foreach( $comments as $comment_ID => $comment )
                                <div id="comment{{ $comment_ID }}" style="margin-top:20px;background:#ccc;">
                                    <div class="row">
                                        <div class="col-xs-2">
                                            {{ $comment_ID }}
                                        </div>
                                        <div class="col-xs-4">
                                            {{ $comment['comment_date'] }}
                                        </div>
                                        <div class="col-xs-3">
                                            {{ $comment['comment_author'] }}
                                        </div>
                                        <div class="col-xs-3">
                                            {{ $comment['comment_type'] }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            {{ $comment['comment_content'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop