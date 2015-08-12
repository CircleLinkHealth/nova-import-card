@extends('app')

@section('content')
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
                        @if (count($comments) > 0)
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