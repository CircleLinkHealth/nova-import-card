@extends('partials.providerUI')

@section('title', 'View all Users')
@section('activity', 'View all Users')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="row">
                <div class="col-sm-8">
                </div>
                <div class="col-sm-4">
                    <div class="pull-right" style="margin:20px;">
                    </div>
                </div>
            </div>

            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">All Users</div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')


                        {!! Form::open(array('url' => route('saas-admin.users.index', array()), 'method' => 'get', 'class' => 'form-horizontal')) !!}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">
                                    <a class="btn btn-info" data-toggle="collapse"
                                       href="#collapseFilter">Toggle Filters</a>
                                </div>
                            </div>
                        </div>
                        <div id="collapseFilter" class="panel-collapse collapse">
                            <div class="row" style="margin:20px 0px 40px 0px;">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="row">
                                        <div class="col-xs-4 text-right">{!! Form::label('filterUser', 'Find User:') !!}</div>
                                        <div class="col-xs-8">{!! Form::select('filterUser', array('all' => 'All') + $users, $filterUser, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-2 text-right">{!! Form::label('filterRole', 'Role:') !!}</div>
                                    <div class="col-xs-4">{!! Form::select('filterRole', array('all' => 'All') + $roles, $filterRole, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                    <div class="col-xs-2 text-right">{!! Form::label('filterProgram', 'Practice:') !!}</div>
                                    <div class="col-xs-4">{!! Form::select('filterProgram', array('all' => 'All') + $programs, $filterProgram, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
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


                        {!! Form::open(array('url' => route('saas-admin.users.action', array()), 'method' => 'post', 'class' => 'form-horizontal')) !!}
                        @if(Cerberus::hasPermission('users-edit-all'))
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        With selected Users:
                                        <select name="action">
                                            <option value="delete">Delete</option>
                                        </select>
                                        <button type="submit" value="Submit" class="btn btn-primary btn-xs"
                                                style="margin-left:10px;"><i
                                                    class="glyphicon glyphicon-circle-arrow-right"></i>
                                            Perform Action
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>Name</strong></td>
                                <td><strong>Role</strong></td>
                                <td><strong>Email</strong></td>
                                <td><strong>Practice</strong></td>
                                <td><strong>Actions</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpUsers) > 0)
                                @foreach( $wpUsers as $wpUser )
                                    <tr>
                                        <td>
                                            <div class="">
                                                <input id="select-user-checkbox-{{ $wpUser->id }}" name="users[]"
                                                       value="{{ $wpUser->id }}" type="checkbox">
                                                <label for="select-user-checkbox-{{ $wpUser->id }}"><span> </span></label>
                                            </div>
                                        </td>
                                        <td><a href="{{ route('saas-admin.users.edit', ['userId' => $wpUser->id]) }}" target="_blank"
                                               class=""> {{ $wpUser->getFullNameWithId() }}</a></td>
                                        <td>
                                            @if ($wpUser->roles->isNotEmpty())
                                                {{ $wpUser->roles->first()->display_name }}
                                            @endif
                                        </td>
                                        <td>{{ $wpUser->email }}</td>
                                        <td>
                                            @if ($wpUser->primaryPractice)
                                                <a href="{{ route('provider.dashboard.manage.notifications', [$wpUser->primaryPractice->name]) }}"
                                                   class=""> {{ $wpUser->primaryPractice->display_name }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-right">
                                            @if($wpUser->isParticipant())
                                                <a href="{{ route('patient.summary', ['patientId' => $wpUser->id]) }}"
                                                   class="btn btn-info btn-xs" style="margin-left:10px;"><i
                                                            class="glyphicon glyphicon-eye-open"></i> View</a>
                                            @endif

                                            @if($wpUser->isCareCoach() || $wpUser->hasRole('saas-admin'))
                                                <a href="{{ route('saas-admin.users.edit', ['userId' => $wpUser->id]) }}"
                                                   class="btn btn-primary btn-xs"><i
                                                            class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @elseif($wpUser->hasRole(['participant']))
                                                <a href="{{ route('patient.demographics.show', ['patientId' => $wpUser->id]) }}"
                                                   class="btn btn-primary btn-xs"><i
                                                            class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @else
                                                <a href="{{ route('provider.dashboard.manage.staff', ['practiceSlug' => $wpUser->practices->first()->name]) }}"
                                                   class="btn btn-primary btn-xs"><i
                                                            class="glyphicon glyphicon-edit"></i> Edit</a>
                                            @endif

                                            @if(auth()->user()->hasPermission('user.delete'))
                                                <a href="{{ route('admin.users.destroy', array('id' => $wpUser->id)) }}"
                                                   onclick="var result = confirm('Are you sure you want to delete?');if (!result) {event.preventDefault();}"
                                                   class="btn btn-danger btn-xs" style="margin-left:10px;"><i
                                                            class="glyphicon glyphicon-remove-sign"></i> Delete</a>
                                            @endif
                                            </div>
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
                        </form>

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
