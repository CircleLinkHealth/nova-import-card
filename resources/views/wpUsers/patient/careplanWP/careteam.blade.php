<?php
$user_info = array();
$new_user = false;
?>

@extends('partials.providerUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">
    <script>
    </script>
    {!! Form::open(array('url' => URL::route('patient.careteam.store', array('patientId' => $patient->id)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <style>
        .careTeamMemberContainer {
            margin-top:30px;
            border-bottom:1px solid #ccc;
        }
    </style>
    <div class="row">
        <div class="icon-container col-lg-12">
            @if(isset($patient) && !$new_user )
                @include('wpUsers.patient.careplan.nav')
            @endif
        </div>
    </div>
    <input type=hidden name=user_id value="{{ $patient->id }}">
    <input type=hidden name=program_id value="{{ $patient->program_id }}">



    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title">
                    @if(isset($patient) && !$new_user )
                        <div class="main-form-title col-lg-12">
                            Edit Patient Care Team
                        </div>
                    @else
                        <div class="main-form-title col-lg-12">
                            Add Patient Care Team
                        </div>
                    @endif
                </div>

                <div class="row" id="careTeamMembers">
                    <div class="col-md-12" style="margin-top:30px;">
                        <div class="row">
                            <div class="col-sm-12">
                                <span class="person-name text-big text-dark text-serif" title="">Care Team Setup</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        @foreach($careTeamUsers as $careTeamUser)
                            <div class="col-md-12" class="careTeamMemberContainer" id="ctm' + ctmCount + '">
                                <div class="row">
                                    <input class="ctmCountArr" type="hidden" name="ctmCountArr[]" value="' + ctmCount + '">
                                    <div class="col-sm-4">';
                                        {!! Form::select('providers', $providersData, (old('providers') ? old('providers') : $careTeamUser->id ? $careTeamUser->id : ''), ['class' => 'form-control selectpicker', 'style' => 'width:50%;']) !!}
                                    </div>
                                    <div class="col-sm-5" id="ctm' + ctmCount + 'Info">
                                    </div>
                                    <div class="col-sm-3">
                                    <a href="" class="removeCtm" ctmId="' + ctmCount + '"><span class="glyphicon glyphicon-remove-sign"></span> Remove Member</a>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio-inline"><input type="checkbox" name="ctmsa[]" id="ctm' + ctmCount + 'sa" /><label for="ctm' + ctmCount + 'sa"><span> </span>Send Alerts</label></div>
                                    </div>
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio"><input type="radio" name="ctbp" id="ctm' + ctmCount + 'bp" /><label for="ctm' + ctmCount + 'bp"><span> </span>Billing Provider</label></div>
                                    </div>
                                    <div class="col-sm-4" style="padding:20px;">
                                    <div class="radio"><input type="radio" name="ctlc" id="ctm' + ctmCount + 'lc" /><label for="ctm' + ctmCount + 'lc"><span> </span>Lead Contact</label></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {!! $phtml !!}
                        <a href="" class="addCareTeamMember pull-right btn btn-orange"><span class="glyphicon glyphicon-plus-sign"></span> Add Care Team Member</a>
                        <br />
                        <br />
                    </div>
                </div>








                <div class="modal fade" id="ctModal" tabindex="-1" role="dialog" aria-labelledby="ctModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                Incomplete Care Team
                            </div>
                            <div class="modal-body">
                                <p><span id="ctModalError"></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="ctModalYes" class="btn btn-warning"  data-dismiss="modal">Continue editing</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ctConfModal" tabindex="-1" role="dialog" aria-labelledby="ctConfModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                Confirm Care Team
                            </div>
                            <div class="modal-body">
                                <p><span id="ctConfModalError"></span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="ctConfModalNo" class="btn btn-warning"  data-dismiss="modal">Continue editing</button>
                                <button type="button" id="ctConfModalYes" class="btn btn-success"  data-dismiss="modal">Confirm and save</button>
                            </div>
                        </div>
                    </div>
                </div>
                @include('wpUsers.patient.careplan.footer')
                <br /><br />
                </form>
            </div>
        </div>
    </div>
@stop

