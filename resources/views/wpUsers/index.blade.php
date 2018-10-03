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
                    $('#withdrawal-note').removeClass('hidden');
                    $('#select-all-container').removeClass('hidden');
                }
                else {
                    $('#withdrawal-note').addClass('hidden');
                    $('#select-all-container').addClass('hidden');
                }

            }

            function onActionSubmit(e) {
                e.preventDefault();

                const form = this.form;

                if (form['action'].value !== "withdraw") {
                    form.submit();
                }
                else {
                    if (form['withdrawal-note-body'].value.length === 0) {
                        alert('Please type a withdrawal note.')
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

                $('#perform-action-submit').on('click', onActionSubmit);
                $('#select-all-in-page').on('change', selectAllUsers);
                $('#select-all-matching-filters').on('click', selectAllMatchingFilters);
            });

        </script>
    @endpush

    <div class="container-fluid">
        <div class="row">
            @include('errors.errors')
            @include('errors.messages')
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
                                {{-- <a href="{{ route('admin.users.createQuickPatient', array('primaryProgramId' => '7')) }}" class="btn btn-success">Participant Quick Add (Program 7)</a> --}}
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
                                <option value="withdraw" selected>Withdraw</option>
                            </select>
                            <button id="perform-action-submit"
                                    type="submit" value="Submit"
                                    class="btn btn-primary btn-xs"
                                    style="margin-left:10px;"><i class="glyphicon glyphicon-circle-arrow-right"></i>
                                Perform Action
                            </button>

                            <div id="withdrawal-note" class="hidden">
                                <textarea id="withdrawal-note-body" rows="7" cols="100"
                                          placeholder="Enter Withdrawal Note..." name="withdrawal-note-body"
                                          required="required" class="form-control"></textarea>
                            </div>

                            <div id="select-all-container" class="hidden">

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
                                        <td><a href="{{ route('admin.users.edit', array('id' => $wpUser->id)) }}"
                                               class=""> {{ $wpUser->fullNameWithID }}</a></td>
                                        <td>
                                            @if (count($wpUser->roles) > 0)
                                                {{$wpUser->roles->unique('display_name')->implode('display_name', ', ')}}
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->email }}</td>
                                        <td>
                                            @if ($wpUser->primaryPractice)
                                                <a href="{{ route('admin.programs.show', array('id' => $wpUser->primaryPractice->id)) }}"
                                                   class=""> {{ $wpUser->primaryPractice->display_name }}</a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if(Cerberus::hasPermission('user.update'))
                                                <a href="{{ route('admin.users.edit', array('id' => $wpUser->id)) }}"
                                                   class="btn btn-primary btn-xs"><i
                                                            class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @endif
                                            @if (count($wpUser->roles) > 0)
                                                @if($wpUser->hasRole('participant'))
                                                    <a href="{{ route('patient.summary', array('patientId' => $wpUser->id)) }}"
                                                       class="btn btn-info btn-xs" style="margin-left:10px;"><i
                                                                class="glyphicon glyphicon-eye-open"></i> UI</a>
                                                @endif
                                            @endif
                                            @if(Cerberus::hasPermission('user.update'))
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
