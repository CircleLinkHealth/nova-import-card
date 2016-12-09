@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('invite.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-7 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">New Invite</div>
                    <div class="panel-body">
                        @if(isset($message))
                            <div class="row">
                                <div class="">
                                    <div class="jumbotron text-center" style="padding: 5px">
                                        <h3><span id="result">{{$message}}</span></h3>
                                    </div>

                                </div>
                            </div>
                        @endif
                        <form class="form-horizontal">
                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="email">Email Address</label>
                                    <div class="col-md-6">
                                        <input id="email" name="email" type="email" placeholder="" class="form-control input-md" required="">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="subject">Subject</label>
                                    <div class="col-md-6">
                                        <input id="subject" name="subject" type="text" placeholder="" class="form-control input-md" required="">

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="body">Body</label>
                                    <div class="col-md-6">
                                        <textarea class="form-control" rows="20" id="body" name="body" required></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="submit"></label>
                                    <div class="col-md-7">
                                        <button id="submit" name="submit" class="btn btn-success">Send Invite!</button>
                                    </div>
                                </div>

                        </form>

                    </div>
                </div>
            </div>


        </div>
@stop
