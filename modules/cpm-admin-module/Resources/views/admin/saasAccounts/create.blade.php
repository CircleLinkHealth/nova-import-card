@extends('cpm-admin::partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add SAAS Account and Admins
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-10 col-md-offset-1">
                                <div class="row">
                                    <div class="col-md-12">
                                        @include('core::partials.errors.errors')
                                        @include('core::partials.errors.messages')
                                    </div>
                                </div>

                                <div id="create-internal-user-form-container">
                                    {!! Form::open(['url' => $submitUrl, 'method' => $submitMethod, 'class' => 'form-horizontal']) !!}

                                    <div role="tabpanel" class="tab-pane active" id="program">
                                        <!-- User info -->
                                        <div id="user-info-formgroup">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-4">{!! Form::label('name', 'SAAS Account Name') !!}
                                                        <span
                                                                style="color: red;">*</span></div>
                                                    <div class="col-xs-6">{!! Form::text('name', '', ['class' => 'form-control', 'required' => true, 'placeholder' => 'eg. Best Call Center Associates']) !!}</div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-xs-4">{!! Form::label('email', 'SAAS Administrators Emails (add a `,` in between)') !!} <span
                                                                style="color: red;">*</span></div>
                                                    <div class="col-xs-6">{!! Form::textarea('admin_emails', '', ['class' => 'form-control', 'required' => true, 'placeholder' => 'eg. email1@saas.com,email2@saas.com,email3@saas.com']) !!}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top:50px;">
                                            <div class="col-sm-12">
                                                <div class="pull-right">
                                                    {!! Form::submit('Invite Users', ['class' => 'btn btn-success submit']) !!}
                                                    <p><u>An Invitation will be sent out to all SAAS Administrators</u></p>
                                                </div>
                                            </div>
                                        </div>

                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection