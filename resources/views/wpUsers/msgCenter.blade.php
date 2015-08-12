@extends('app')

@section('content')
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,700">
    <link rel="stylesheet"  href="//getbootstrap.com/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet"  href="//demos.jquerymobile.com/1.4.5/css/themes/default/jquery.mobile-1.4.5.min.css"/>
    <link rel="stylesheet" href="//demos.jquerymobile.com/1.4.5/_assets/css/jqm-demos.css"/>

    <script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/_assets/js/index.js"></script>
    <script type='text/javascript' src='//code.jquery.com/jquery-2.1.4.js'></script>
    <script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/js/jquery.mobile-1.4.5.min.js"></script>
    <script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/js/jquery.js"></script>
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
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (count($messages) > 0)
                            <div class="alert alert-success">
                                <strong>Messages:</strong><br><br>
                                <ul>
                                    @foreach ($messages as $message)
                                        <li>{{ $message }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif






                        <h2>App Simulator:</h2>
                        @if (count($cpFeed['CP_Feed']) == 0)
                            No feed data to show?
                        @else
                            @foreach( $cpFeed['CP_Feed'] as $key => $value )
                                <div class="row col-lg-12" data-role="collapsible" data-theme="b">
                                    <h2>{{ $cpFeed['CP_Feed'][$key]['Feed']['FeedDate'] }}</h2>
                                    @foreach( $cpFeedSections as $section )
                                        @if ($section == 'Symptoms')
                                           <div class="row col-lg-12 col-lg-offset-2" data-role="collapsible" data-theme="b"><h3>Would you like to report any Symptoms?</h3>
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
                                        @endif
                                    @endforeach
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