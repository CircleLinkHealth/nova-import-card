@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => route('invite.store', array()), 'class' => 'form-horizontal')) !!}
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
                                    <input id="email" name="email" type="email" placeholder=""
                                           class="form-control input-md" required="">

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" for="subject">Subject</label>
                                <div class="col-md-6">
                                    <input id="subject" name="subject" type="text"
                                           value="Invitation to join CircleLink's CarePlanManager"
                                           class="form-control input-md" required="">

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" for="body">Body</label>
                                <div class="col-md-6">
                                    <textarea class="form-control" rows="20" id="body" name="body" required>Welcome to CircleLink's CarePlanManager for preventative care and/or CCM! Please click on below button to input your practice information (team, location, EHR login etc.). If you'd prefer to send us that information separately through a spreadsheet or other medium, no problem! Just e-mail contact@circlelinkhealth
                                        .com with your request.&#013;&#010;&#013;&#010;Once your practice is setup in our system and we have a list of your patients, we'll begin enrolling patients.&#013;&#010;&#013;&#010;Look forward to kicking things off!&#013;&#010;&#013;&#010;CircleLink Team</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" for="submit"></label>
                                <div class="col-md-7">
                                    <button id="submit" name="submit" class="btn btn-success">Send Invite!</button>
                                </div>
                            </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
