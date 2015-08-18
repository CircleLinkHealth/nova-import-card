@extends('app')

@section('content')
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,700">
    <link rel="stylesheet"  href="//getbootstrap.com/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet"  href="//demos.jquerymobile.com/1.4.5/css/themes/default/jquery.mobile-1.4.5.min.css"/>
    <link rel="stylesheet" href="//demos.jquerymobile.com/1.4.5/_assets/css/jqm-demos.css"/>
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
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        User ID: {{ $wpUser->ID }}
                    </div>
                    <div class="panel-body">
                        @if (isset($error))
                            <div class="alert alert-danger">
                                <ul>
                                    <li>{{ $error }}</li>
                                </ul>
                            </div>
                        @endif

                        @if (isset($success))
                            <div class="alert alert-success">
                                <ul>
                                    <li>{{ $success }}</li>
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="pull-left" style="margin-left:10px;">
                                <a href="{{ url('wpusers/'.$wpUser->ID.'/msgcenter?action=run_scheduler&date=today') }}" class="btn btn-primary">Run Scheduler</a>
                            </div>
                        </div>

                        <h2>App Simulator:</h2>
                        @if (count($cpFeed['CP_Feed']) == 0)
                            No feed data to show?
                        @else
                            @foreach( $cpFeed['CP_Feed'] as $key => $value )
                                <div class="row col-lg-12" style="border:3px solid #286090;margin:20px 0px;">
                                    <button class="btn btn-info" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}" aria-expanded="false" aria-controls="collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}">
                                        {{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}
                                    </button>
                                    <div class="collapse" id="collapse{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}">
                                    @foreach( $cpFeedSections as $section )
                                        @if ($section == 'Symptoms')
                                            <button class="btn btn-info" style="margin:20px 0px;" type="button" data-toggle="collapse" data-target="#collapse{{ $key.'-'.$section }}" aria-expanded="false" aria-controls="collapse{{ $key.'-'.$section }}">
                                                {{ $section }}
                                            </button>
                                            <div class="row">
                                            <div class="col-lg-12 collapse" id="collapse{{ $key.'-'.$section }}" style="background:#ddd;">
                                               <h3>Symptoms</h3>
                                        @endif

                                           @foreach( $cpFeed['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio )
                                               {!! $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['formHtml'] !!}
                                               @if (isset($arrBio['Response']))
                                                       {!! $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['formHtml'] !!}
                                               @endif
                                               @if (isset($arrBio['Response']['Response']))
                                                   {!! $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['Response']['formHtml'] !!}
                                               @endif
                                           @endforeach

                                        @if ($section == 'Symptoms')
                                            </div>
                                            </div>
                                        @endif
                                        <div style="clear:both;"></div>
                                    @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif











                        <div class="row">
                            {!! Form::open(array('url' => '/wpusers/'.$wpUser->ID.'/edit', 'class' => 'form-horizontal')) !!}
                        </div>
                        <h2>Respond</h2>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-2">New Message</div>
                                <div class="col-xs-10">
                                    {!! Form::textarea('msgText','Enter text here',['class'=>'form-control', 'rows' => 3, 'cols' => 10]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::submit('Send Text', array('class' => 'btn btn-success', 'disabled' => 'disabled')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

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