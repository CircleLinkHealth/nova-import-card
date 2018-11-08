@extends('partials.adminUI')

@section('content')

@push('scripts')
<script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#togglePrograms").click(function (event) {
                event.preventDefault();
                $("#programs").toggle();
                return false;
            });

            $(function () {
                $("#programsCheckAll").click(function () {
                    $(".programs").prop("checked", true);
                    return false;
                });

                $("#programsUncheckAll").click(function () {
                    $(".programs").prop("checked", false);
                    return false;
                });
            });
        });
    </script>
@endpush
@push('styles')
    <style>
        .form-group {
            margin:20px !important;
        }
    </style>
@endpush
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New User</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Create User
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::open(array('url' => route('admin.users.store'), 'class' => 'form-horizontal')) !!}

                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                                        <li role="presentation" class="active"><a href="#program" aria-controls="program" role="tab"
                                                                                data-toggle="tab">Practice</a></li>
                                    </ul>

                                    <div class="tab-content">
                                        <div role="tabpanel" class="tab-pane active" id="program">
                                            <h2>User Info</h2>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('username', 'Login:') !!}</div>
                                                    <div class="col-xs-10">{!! Form::text('username', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('email', 'email:') !!}</div>
                                                    <div class="col-xs-10">{!! Form::text('email', '', ['class' => 'form-control', 'style' => 'width:80%;']) !!}</div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('password', 'Password:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::password('password', ['class' => 'form-control']) !!}</div>
                                                    <div class="col-xs-2">{!! Form::label('password_confirmation', 'Confirm Password:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::password('password_confirmation', ['class' => 'form-control']) !!}</div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::text('first_name', '', ['class' => 'form-control']) !!}</div>
                                                    <div class="col-xs-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::text('last_name', '', ['class' => 'form-control']) !!}</div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('user_status', 'User Status:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::select('user_status', array('0' => '0', '1' => '1'), 1, ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                                    <div class="col-xs-2">{!! Form::label('google_drive_folder', 'Google Drive Folder (report writers only):') !!}</div>
                                                    <div class="col-xs-4">{!! Form::text('google_drive_folder', '', ['class' => 'form-control']) !!}</div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('ccm_status', 'CCM Status:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::select('ccm_status', array('enrolled' => 'Enrolled', 'paused' => 'Paused', 'withdrawn' => 'Withdrawn', 'to_enroll' => 'To Enroll'), '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                                    <div class="col-xs-2">{!! Form::label('careplan_status', 'Careplan Status:') !!}</div>
                                                    <div class="col-xs-4">{!! Form::select('careplan_status', array('draft' => 'Draft', 'qa_approved' => 'QA Approved', 'provider_approved' => 'Provider Approved'), '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                                </div>
                                            </div>

                                            <h2>Role</h2>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('role', 'Role:') !!}</div>
                                                    <div class="col-xs-10">{!! Form::select('role', $roles, '', ['class' => 'form-control select-picker', 'style' => 'width:40%;']) !!}</div>
                                                </div>
                                            </div>



                                            <h2><a data-toggle="collapse" data-target="#programCollapse" class="">Programs</a></h2>

                                            <div id="programCollapse" class="collapse in" style="background:#888;padding:20px;">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-xs-2">{!! Form::label('program_id', 'Primary Practice:') !!}</div>
                                                        <div class="col-xs-4">{!! Form::select('program_id', $wpBlogs, '', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                                        <div class="col-xs-2">{!! Form::label('provider_id', 'Billing Provider:') !!}</div>
                                                        <div class="col-xs-4">{!! Form::select('provider_id', [], '', ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                                        <div class="col-xs-6"></div>
                                                        <div class="col-xs-4">{!! Form::label('auto_attach_programs', 'Give access to all of ' . auth()->user()->saasAccountName() . '\'s practices') !!}</div>
                                                        <div class="col-xs-2">
                                                            {!! Form::checkbox('auto_attach_programs', 0, 0) !!}
                                                        </div>
                                                        @push('scripts')
                                                            <script>
                                                                (function () {
                                                                    function setBillingProvider(practiceId) {
                                                                        return $.ajax({
                                                                            url: '/api/practices/' + practiceId + '/providers',
                                                                            type: 'GET',
                                                                            success: function (providers) {
                                                                                console.log('practice:providers', providers)
                                                                                $('[name="provider_id"]').html('')
                                                                                providers.forEach(function (provider) {
                                                                                    $('[name="provider_id"]').append($('<option />').val(provider.id).text(provider.name))
                                                                                })
                                                                            }
                                                                        })
                                                                    }
                
                                                                    $('[name="program_id"]').change(function () {
                                                                        setBillingProvider($(this).val())
                                                                    })
                
                                                                    setBillingProvider($('[name="program_id"]').val())
                                                                })();
                                                                
                                                            </script>
                                                        @endpush
                                                    </div>
                                                </div>


                                                <a class="btn btn-info panel-title" href="#" id="togglePrograms"><strong>Toggle Programs list</strong></a><br /><br />
                                                <div id="programs" style="display:none;">
                                                    <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button> |
                                                    <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>
                                                    @foreach( $wpBlogs as $wpBlogId => $domain )
                                                        <div class="row" id="program_{{ $wpBlogId }}" style="border-bottom:1px solid #000;">
                                                            <div class="col-sm-2">
                                                                <div class="text-right">
                                                                {!! Form::checkbox('programs[]', $wpBlogId, [], ['style' => '', 'class' => 'programs']) !!}
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-10">{!! Form::label('Value', 'Practice: '.$domain, array('class' => '')) !!}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <h2>Location (for API Users)</h2>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-xs-2">{!! Form::label('id', 'Location:') !!}</div>
                                                    <div class="col-xs-10">{!! Form::select('location_id', ['default' => 'Attach a location to API users'] + $locations, 'default', ['class' => 'form-control select-picker', '', 'style' => 'width:80%;']) !!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row" style="margin-top:50px;">
                                        <div class="col-sm-12">
                                            <div class="pull-right">
                                                <a href="{{ route('admin.users.index', array()) }}" class="btn btn-danger">Cancel</a>
                                                {!! Form::submit('Create User', array('class' => 'btn btn-success')) !!}
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop