@extends('partials.adminUI')

@section('content')

    @push('styles')

        <style>
            .hidden {
                display: none;
            }

            #withdrawal-note {
                margin-top: 5px;
                margin-bottom: 5px;
            }

            .withdrawn-input {
                margin-top: 10px;
            }

            a {
                cursor: pointer;
            }
        </style>

    @endpush

    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/wpUsers/wpUsers.js') }}"></script>
        <script>

            function onActionChange(e) {

                if (e.target.value === "withdraw") {
                    $('#withdrawn-reason').removeClass('hidden');
                    $('#select-all-container').removeClass('hidden');
                }
                else {
                    $('#withdrawn-reason').addClass('hidden');
                    $('#withdrawn-reason-other').addClass('hidden');
                    $('#select-all-container').addClass('hidden');
                }

            }

            function onReasonChange(e){

                if (e.target.value === "Other") {
                    $('#withdrawn-reason-other').removeClass('hidden');
                }
                else {
                    $('#withdrawn-reason-other').addClass('hidden');
                }

            }

            function onActionSubmit(e) {
                e.preventDefault();

                const form = this.form;

                if (form['action'].value !== "withdraw") {
                    form.submit();
                }
                else {
                    if (form['withdrawn-reason'].value == 'Other' && form['withdrawn-reason-other'].value.length === 0) {
                        alert('Please type a withdrawal reason in the textbox. Otherwise, select a different reason from the dropdown.')
                    }
                    else if (confirm('Are you sure?')) {
                        form.submit();
                    }
                }
            }

            function selectAllUsers(e) {
                const checked = e.target.checked;
                const checkboxes = $('.user-select-checkbox');
                checkboxes.prop('checked', checked);

                removeFiltersFromForm();

                if (checked) {
                    $('#select-all-matching-filters').removeClass('hidden');
                    $('#select-all-in-page-label').text(`${checkboxes.length} users selected.`);
                }
                else {
                    $('#select-all-matching-filters').addClass('hidden');
                    $('#select-all-in-page-label').text(`Select all`);
                }

            }

            function selectAllMatchingFilters() {
                applyFiltersToForm();
                $('#select-all-matching-filters').addClass('hidden');
                $('.user-select-checkbox').prop('checked', true);
                $('#select-all-in-page-label').text(`All users matching the filters are selected`);
            }

            function applyFiltersToForm() {
                const form = $('#form-do-action')[0];
                form['filterRole'].value = $('#filterRole').val();
                form['filterProgram'].value = $('#filterProgram').val();
            }

            function removeFiltersFromForm() {
                const form = $('#form-do-action')[0];
                form['filterRole'].value = "";
                form['filterProgram'].value = "";
            }

            $('document').ready(function () {

                const actionSelectEl = $('#perform-action-select');
                actionSelectEl.on('change', onActionChange);
                actionSelectEl.change();

                const reasonSelectEl = $('#perform-reason-select');
                reasonSelectEl.on('change', onReasonChange);
                reasonSelectEl.change();

                $('#perform-action-submit').on('click', onActionSubmit);
                $('#select-all-in-page').on('change', selectAllUsers);
                $('#select-all-matching-filters').on('click', selectAllMatchingFilters);
            });

        </script>
    @endpush

    <div class="container-fluid">
        <div class="row">
            @include('core::partials.errors.errors')
            @include('core::partials.core::partials.errors.messages')
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-2">
                        <h1>Users</h1>
                    </div>
                    @if(Cerberus::hasPermission('user.create'))
                        <div class="col-sm-10">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ route('admin.users.create', array()) }}" class="btn btn-success">New
                                    User</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Users</div>
                    <div class="panel-body">
                        <div class="row">
                            {!! Form::open(array('url' => route('admin.users.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                            <a class="btn btn-info panel-title" data-toggle="collapse" data-parent="#accordion"
                               href="#collapseFilter">Toggle Filters</a><br/><br/>
                            <div id="collapseFilter" class="panel-collapse collapse">
                                <div class="row" style="margin:20px 0px 40px 0px;">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="row">
                                            <div class="col-xs-4 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                            <div class="col-xs-8">{!! Form::select('filterUser', array('all' => 'All Users') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-xs-2 text-right">{!! Form::label('filterRole', 'Role:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('filterRole', array('all' => 'All Roles') + $roles, $filterRole, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        <div class="col-xs-2 text-right">{!! Form::label('filterProgram', 'Program:') !!}</div>
                                        <div class="col-xs-4">{!! Form::select('filterProgram', array('all' => 'All Programs') + $programs, $filterProgram, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:50px;">
                                    <div class="col-sm-12">
                                        <div class="" style="text-align:center;">
                                            {!! Form::hidden('action', 'filter') !!}
                                            <button type="submit" class="btn btn-primary"><i
                                                        class="glyphicon glyphicon-sort"></i> Apply Filters
                                            </button>
                                            <button type="submit" class="btn btn-primary"><i
                                                        class="glyphicon glyphicon-refresh"></i> Reset Filters
                                            </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>


                        {!! Form::open(array('url' => route('admin.users.doAction', array()), 'id' => 'form-do-action', 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        @if(Cerberus::hasPermission('user.update'))
                            Selected User Actions:
                            <select id="perform-action-select" name="action">
                                <option value="delete">Delete</option>
                                <option value="enroll">Enroll</option>
                                <option value="unreachable" selected>Mark As Unreachable</option>
                                <option value="withdraw" selected>Withdraw</option>
                            </select>


                            <div id="withdrawn-reason" class="hidden withdrawn-input">
                                Select Withdrawn Reason:
                                <select id="perform-reason-select" name="withdrawn-reason" >
                                    <option value="No Longer Interested">No Longer Interested</option>
                                    <option value="Moving out of Area">Moving out of Area</option>
                                    <option value="New Physician">New Physician</option>
                                    <option value="Cost / Co-Pay">Cost / Co-Pay</option>
                                    <option value="Changed Insurance">Changed Insurance</option>
                                    <option value="Dialysis / End-Stage Renal Disease">Dialysis / End-Stage Renal Disease</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Patient in Hospice">Patient in Hospice</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div id="withdrawn-reason-other" class="hidden withdrawn-input">
                                <textarea id="withdrawn-reason-other" rows="5" cols="100"
                                          placeholder="Enter Withdrawal Reason..." name="withdrawn-reason-other"
                                          required="required" class="form-control"></textarea>
                            </div>

                        <div>
                            <button id="perform-action-submit"
                                    type="submit" value="Submit"
                                    class="btn btn-primary btn-xs"
                                    style="margin-top:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i>
                                Perform Action
                            </button>
                        </div>

                            <div id="select-all-container" class="hidden withdrawn-input">

                                <input type="checkbox" id="select-all-in-page"/>
                                <label for="select-all-in-page" id="select-all-in-page-label">Select all</label>

                                <input type="hidden" name="filterRole"/>
                                <input type="hidden" name="filterProgram"/>

                                <span id="select-all-matching-filters" class="hidden">
                                <a>Select everything</a> matching the filters.
                                </span>

                            </div>
                        @endif
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>Name</strong></td>
                                <td><strong>Role</strong></td>
                                <td><strong>Email</strong></td>
                                <td><strong>Program</strong></td>
                                <td><strong>Actions</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpUsers) > 0)
                                @foreach( $wpUsers as $wpUser )
                                    <tr>
                                        <td><input class="user-select-checkbox" type="checkbox" name="users[]"
                                                   value="{{ $wpUser->id }}"></td>
                                        <td>
                                            @if(!App\Http\Controllers\SuperAdmin\UserController::hideFromAdminPanel($wpUser))
                                                <a href="{{ route('admin.users.edit', array('id' => $wpUser->id)) }}"
                                                   class=""> {{ $wpUser->getFullNameWithId() }}</a>
                                            @else
                                                {{$wpUser->getFullNameWithId()}}
                                            @endif
                                        </td>
                                        <td>
                                            {{$wpUser->roles->unique('display_name')->implode('display_name', ', ')}}

                                            @if($wpUser->isParticipant() && $wpUser->patientInfo)
                                                ({{ucfirst($wpUser->patientInfo->ccm_status)}})
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->email }}</td>
                                        <td>
                                            @if ($wpUser->primaryPractice)
                                                <a href="{{ route('provider.dashboard.manage.notifications', [$wpUser->primaryPractice->name]) }}"
                                                   class=""> {{ $wpUser->primaryPractice->display_name }}</a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if(!App\Http\Controllers\SuperAdmin\UserController::hideFromAdminPanel($wpUser) && auth()->user()->hasPermission('user.update'))
                                                <a href="{{ route('admin.users.edit', array('id' => $wpUser->id)) }}"
                                                   class="btn btn-primary btn-xs"><i
                                                            class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @endif

                                            @if($wpUser->isParticipant())
                                                <a href="{{ route('patient.summary', array('patientId' => $wpUser->id)) }}"
                                                   class="btn btn-info btn-xs" style="margin-left:10px;"><i
                                                            class="glyphicon glyphicon-eye-open"></i> View Chart</a>
                                            @endif

                                            @if(auth()->user()->hasPermission('user.update'))
                                                <a href="{{ route('admin.users.destroy', array('id' => $wpUser->id)) }}"
                                                   onclick="var result = confirm('Are you sure you want to delete?');if (!result) {event.preventDefault();}"
                                                   class="btn btn-danger btn-xs" style="margin-left:10px;"><i
                                                            class="glyphicon glyphicon-remove-sign"></i> Delete</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7">No users found</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        {!! Form::close() !!}

                        @if (count($wpUsers) > 0)
                            {!! $wpUsers->appends(['action' => 'filter', 'filterUser' => $filterUser, 'filterRole' => $filterRole, 'filterProgram' => $filterProgram])->render() !!}
                        @endif

                        @if (count($invalidUsers) > 0)
                            <h2>Invalid Users</h2>
                            <h3>Missing Config</h3>
                            @foreach( $invalidUsers as $user )
                                User {{ $user->id }} - {{ $user->display_name }}<br>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
