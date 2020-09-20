@extends('partials.adminUI')

@section('content')
    @push('styles')
        <style>
            .form-group {
                margin: 20px !important;
            }
        </style>
    @endpush

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Create New User</h1>
                    </div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')

                        {!! Form::open(array('url' => route('admin.users.store'), 'class' => 'form-horizontal')) !!}

                        <div class="row">
                            <div class="col-xs-12 col-md-9">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('username', 'Username (for login)') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::text('username', '', ['class' => 'form-control', 'placeholder' => 'jane.doe678']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('email', 'Email:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::text('email', '', ['class' => 'form-control', 'placeholder' => 'jane.doe@example.com']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('role', 'Role:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::select('role', $roles, '', ['class' => 'form-control select-picker']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group" id="care-coach-start-date" style="visibility: hidden;">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('start_date', 'Start Date:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::date('start_date', now(), ['class' => 'form-control', 'placeholder' => 'Official start date']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('password', 'Password:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::password('password', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('password_confirmation', 'Confirm Password:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::password('password_confirmation', ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                                <br>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('first_name', 'First Name:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::text('first_name', '', ['class' => 'form-control', 'placeholder' => 'Jane']) !!}</div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                        <div class="col-xs-12 col-md-6">{!! Form::text('last_name', '', ['class' => 'form-control', 'placeholder' => 'Doe']) !!}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div id="programCollapse" class="collapse in" style="background:#eee;padding:20px;">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-2">{!! Form::label('program_id', 'Primary Practice:') !!}</div>
                                        <div class="col-xs-12 col-md-4">{!! Form::select('program_id', $wpBlogs, '', ['class' => 'form-control select-picker']) !!}</div>
                                        <div class="col-xs-12 col-md-6"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-11 col-md-2">{!! Form::label('auto_attach_programs', 'Give access to all of ' . auth()->user()->saasAccountName() . '\'s practices') !!}</div>
                                        <div class="col-xs-1">{!! Form::checkbox('auto_attach_programs', 0, 0) !!}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-11 col-md-2">{!! Form::label('can_see_phi', 'Grant access to see PHI.') !!}</div>
                                        <div class="col-xs-1">{!! Form::checkbox('can_see_phi', 1, 1, ['class' => 'form-check-input']) !!}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <button class="btn btn-info panel-title" id="togglePrograms"><strong>Toggle
                                                    Practices list</strong></button>
                                        </div>
                                    </div>
                                </div>
                                <div id="programs" class="row" style="display:none;">
                                    <div class="form-group">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6 col-md-offset-1">
                                                    <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button>
                                                    |
                                                    <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>
                                                </div>
                                            </div>
                                            <br>
                                            <br>
                                            @foreach( $wpBlogs as $wpBlogId => $domain )
                                                <div class="row" id="program_{{ $wpBlogId }}">
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
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                $("#togglePrograms").click(function (event) {
                    event.preventDefault();
                    $("#programs").toggle();
                });

                $("#programsCheckAll").click(function () {
                    $(".programs").prop("checked", true);
                    return false;
                });

                $("#programsUncheckAll").click(function () {
                    $(".programs").prop("checked", false);
                    return false;
                });

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

                const CLH_CARE_COACH = 'CLH Care Coach';
                $('[name="role"]').change(function () {
                    const roleName = $('[name="role"] option:selected').text();

                    if (roleName === CLH_CARE_COACH) {
                        $('#care-coach-start-date').css('visibility', 'visible');
                    }
                    else {
                        $('#care-coach-start-date').css('visibility', 'hidden');
                    }
                });

                setBillingProvider($('[name="program_id"]').val())
            })();
        </script>
    @endpush
@stop
