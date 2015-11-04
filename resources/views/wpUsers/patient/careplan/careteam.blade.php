@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/patient/careplan.js') }}"></script>
    <link href="{{ asset('/css/wpstyle.css') }}" rel="stylesheet">


    <?php
        $user_info = array();
        $new_user = false;
    ?>

    {!! Form::open(array('url' => URL::route('patient.careteam.store', array('patientId' => $patient->ID)), 'class' => 'form-horizontal', 'id' => 'ucpForm')) !!}
    <style>
        .careTeamMemberContainer {
            margin-top:30px;
            border-bottom:1px solid #ccc;
        }
    </style>


    <input type=hidden name=user_id value="{{ $patient->ID }}">
    <input type=hidden name=program_id value="{{ $patient->program_id }}">
    <div class="container">
        <section class="main-form">
            <div class="row">
                <?php //include('patient-nav-cp.php'); ?>
                <div class="">
                    <div class="row">
                        <div class="icon-container col-lg-12">
                            @if(isset($patient) && !$new_user )
                                @include('wpUsers.patient.careplan.nav')
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="icon-container col-lg-12">&nbsp;
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2">
                    <div class="row">
                        <div class="main-form-title col-lg-12">
                            Edit Patient Care Team
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="main-form-container col-lg-8 col-lg-offset-2">
                    <div class="row" id="careTeamMembers">
                        <div class="col-md-12" style="margin-top:30px;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <span class="person-name text-big text-dark text-serif" title="">Care Team Setup</span>
                                </div>
                            </div>
                        </div>



                        <div class="col-md-12" class="careTeamMemberContainer" id="ctm0">
                            <div class="row">
                                <div class="col-sm-4">
                                    <?php /* echo buildProviderDropDown($providers); */ ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                COL6 - provider info, static
                            </div>
                            <div class="col-sm-2">
                                <a href="" class="removeCtm" ctmId="0">Remove</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4" style="padding:20px;">
                                <div class="radio-inline"><input type="checkbox" id="cheeb1a" /><label for="cheeb1a"><span> </span>Send Alerts</label></div>
                            </div>
                            <div class="col-sm-4" style="padding:20px;">
                                <div class="radio"><input type="radio" name="billprov" id="cheeb2a" /><label for="cheeb2a"><span> </span>Billing Provider</label></div>
                            </div>
                            <div class="col-sm-4" style="padding:20px;">
                                <div class="radio"><input type="radio" name="leadContact" id="cheeb3a" /><label for="cheeb3a"><span> </span>Lead Contact</label></div>
                            </div>
                        </div>
                    </div>
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



            <div class="row">
                <div class="col-md-12" style="margin-top:10px;">
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="" class="addCareTeamMember"><span class="glyphicon glyphicon-plus-sign"></span> Add Care Team Member</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="margin-top:30px;">
                    <div class="row">
                        <div class="col-sm-12">

                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="margin-top:30px;">
                </div>



            </div>
            <?php /* echo buildProviderInfoContainers($providers, $blog_id); */ ?>


            @include('wpUsers.patient.careplan.footer')
            <br /><br />
        </section>
    </div>
    </form>
@stop
