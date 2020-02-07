@extends('partials.adminUI')

@section('content')
    @push('scripts')
    <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
    <script>
        $(document).ready(function () {
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
            margin: 20px;
        }
    </style>
    @endpush
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1><span>Editing </span>{{ $patient->getFullName() }}</h1>
                <div class="panel panel-default">
                    <div class="panel-body">

                        @include('errors.errors')

                        {!! Form::open(array('url' => route('admin.users.update', array('id' => $patient->id)), 'class' => 'form-horizontal')) !!}

                        <div class="row" style="">
                            <div class="col-sm-12">
                                @if($patient->isParticipant())
                                    <div class="pull-left" style="margin-left:10px;">
                                        <a href="{{ route('patient.summary', array('patientId' => $patient->id)) }}"
                                           class="btn btn-info">Go To Provider UI</a>
                                    </div>
                                @endif
                                <div class="pull-right">
                                    <a href="{{ route('admin.users.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top:20px;">
                            <li role="presentation" class="active">
                                <a href="#program" aria-controls="program" role="tab" data-toggle="tab">User Info</a>
                            </li>
                            @if($patient->isCareCoach() && $patient->nurseInfo)
                                <li role="presentation">
                                    <a href="#nurseinfo" aria-controls="nurseinfo" role="tab" data-toggle="tab">Nurse
                                        Info</a>
                                </li>
                            @endif

                            @if($patient->hasRole('care-ambassador') && $patient->careAmbassador)
                                <li role="presentation">
                                    <a href="#careAmbassador" aria-controls="careAmbassador" role="tab"
                                       data-toggle="tab">Care Ambassador Settings</a>
                                </li>
                            @endif

                            <li role="presentation">
                                <a href="#revisions" aria-controls="revisions" role="tab" data-toggle="tab">History</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" id="program">

                                <h2>User Info</h2>
                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('username', 'Login:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('username', $patient->username, ['class' => 'form-control']) !!}</div>

                                    <div class="col-xs-1">{!! Form::label('email', 'Email:') !!}</div>
                                    <div class="col-xs-3">{!! Form::email('email', $patient->email, ['class' => 'form-control']) !!}</div>

                                    <div class="col-xs-1">{!! Form::label('role', 'Role:') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('role', $roles, $role->id, ['class' => 'form-control select-picker']) !!}</div>
                                </div>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('first_name', 'First Name:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('first_name', $patient->getFirstName(), ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('last_name', 'Last Name:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('last_name', $patient->getLastName(), ['class' => 'form-control']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('suffix', 'Suffix:') !!}</div>
                                    <div class="col-xs-3">{!! Form::text('suffix', $patient->suffix, ['class' => 'form-control']) !!}</div>
                                </div>

                                <br>

                                <div class="form-group">
                                    <div class="col-xs-1">{!! Form::label('access_disabled', 'Access Disabled') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('access_disabled', array('0' => 'No', '1' => 'Yes'), $patient->access_disabled, ['class' => 'form-control select-picker']) !!}</div>
                                    <div class="col-xs-1">{!! Form::label('timezone', 'Timezone:') !!}</div>
                                    <div class="col-xs-3">{!! Form::select('timezone',
                                    [
                                    'America/New_York' => 'Eastern Time',
                                    'America/Chicago' => 'Central Time',
                                    'America/Denver' => 'Mountain Time',
                                    'America/Phoenix' => 'Mountain Time (no DST)',
                                    'America/Los_Angeles' => 'Pacific Time',
                                    'America/Anchorage' => 'Alaska Time',
                                    'America/Adak' => 'Hawaii-Aleutian',
                                    'Pacific/Honolulu' => 'Hawaii-Aleutian Time (no DST)',
                                    ],
                                    $patient->timezone, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                </div>



                                <h2><a data-toggle="collapse" data-target="#programCollapse" class="">Practices</a></h2>

                                <div id="programCollapse" class="collapse in" style="background:#eeeeee;padding:20px;">
                                    <div class="form-group">
                                        <div class="col-xs-2">{!! Form::label('program_id', 'Primary Practice') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('program_id', $wpBlogs, $primaryBlog, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="row">
                                                <div class="col-xs-8">{!! Form::label('auto_attach_programs', 'Give access to all of ' . auth()->user()->saasAccountName() . '\'s practices') !!}</div>
                                                <div class="col-xs-4">
                                                    {!! Form::checkbox('auto_attach_programs', 1, !! $patient->auto_attach_programs) !!}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-8">{!! Form::label('can_see_phi', 'Grant access to see PHI:') !!}</div>
                                                <div class="col-xs-4">{!! Form::checkbox('can_see_phi', 0, $patient->canSeePhi(), ['class' => 'form-check-input']) !!}</div>
                                            </div>
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
                                                                $('[name="provider_id"]').val($('[name="provider_id"]').attr('value'))
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


                                    <a class="btn btn-info panel-title" href="#" id="togglePrograms"><strong>Toggle
                                            Practices list</strong></a><br/><br/>
                                    <div id="programs" style="display:none;">
                                        <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button>
                                        |
                                        <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>

                                        @foreach( $wpBlogs as $wpBlogId => $domain )
                                            <div class="row" id="program_{{ $wpBlogId }}">
                                                <div class="col-sm-2">
                                                    <div class="text-right">
                                                        @if( in_array($wpBlogId, $userPractices) )
                                                            {!! Form::checkbox('programs[]', $wpBlogId, ['checked' => "checked"], ['style' => '', 'class' => 'programs']) !!}
                                                        @else
                                                            {!! Form::checkbox('programs[]', $wpBlogId, [], ['style' => '', 'class' => 'programs']) !!}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">{!! Form::label('Value', 'Program: '.$domain, array('class' => '')) !!}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if($patient->isProvider())
                                <div role="tabpanel" class="tab-pane" id="providerinfo">
                                    <h2>Provider Info</h2>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('npi_number', 'NPI Number:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('npi_number', $patient->getNpiNumber(), ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('specialty', 'Specialty:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('specialty', $patient->getSpecialty(), ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                    </div>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('prefix', 'Prefix(DEPR?):') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('prefix', $patient->getPrefix(), ['class' => 'form-control']) !!}</div>
                                    </div>
                                </div>
                            @endif


                            @if($patient->isCareCoach() && $patient->nurseInfo)
                                <div role="tabpanel" class="tab-pane" id="nurseinfo">
                                    <h2>Nurse Info</h2>
                                    @include('partials.admin.user.nurse-info', ['nurseInfo' =>  $patient->nurseInfo])
                                </div>
                            @endif

                            @if($patient->hasRole('care-ambassador'))
                                <div role="tabpanel" class="tab-pane" id="careAmbassador">
                                    <h2>Care Ambassador Info</h2>
                                    <div class="form-group">
                                            <div class="col-xs-2">{!! Form::label('hourly_rate', 'Hourly Rate:') !!}</div>
                                            <div class="col-xs-10">{!! Form::text('hourly_rate', $patient->careAmbassador->hourly_rate, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                                            <div class="col-xs-2" style="padding-top: 20px">{!! Form::label('speaks_spanish', 'Spanish Speaking:') !!}</div>
                                            <div class="col-xs-10"style="padding-top: 20px"><input type="checkbox"
                                                                          @if($patient->careAmbassador->speaks_spanish) checked
                                                                          @endif name="speaks_spanish"
                                                                          id="speaks_spanish"></div>
                                    </div>
                                </div>
                            @endif


                            <div role="tabpanel" class="tab-pane" id="revisions">
                                @include('partials.revisions')
                            </div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::previous() }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::hidden('user_id', $patient->id) !!}
                                    {!! Form::submit('Update User', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
{!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop