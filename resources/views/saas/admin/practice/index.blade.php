@extends('partials.providerUI')

@section('title', 'Manage Practices')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                    </div>
                    @if(Cerberus::hasPermission('practice.read'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ route('saas-admin.practices.create')}}" class="btn btn-success">New
                                    Practice</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Practice</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Patients</strong></td>
                                <td><strong>Created</strong></td>
                                <td><strong></strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($practices) > 0)
                                @foreach( $practices as $practice )
                                    <tr>
                                        <td>
                                            <a href="{{ route('provider.dashboard.index', ['practiceSlug' => $practice->name]) }}"
                                               class=""><strong>{{ $practice->display_name }}</strong></a></td>
                                        <td>
                                            @if (count($practice->users) > 0)
                                                <a href="{{ route('admin.users.index', array('filterProgram' => $practice->id)) }}"
                                                   class=""><strong>{{ count($practice->users()->whereHas('roles', function ($q) {
					$q->where('name', '=', 'participant');
				})->get()) }}</strong></a>
                                            @endif
                                        </td>
                                        <td>{{ date('F d, Y g:i A', strtotime($practice->created_at)) }}</td>
                                        <td class="text-right">
                                            @if(Cerberus::hasPermission('practice.read'))
                                                <a href="{{ route('provider.dashboard.index', ['practiceSlug' => $practice->name]) }}"
                                                   class="btn btn-xs btn-success">
                                                    Edit Settings / Add Staff
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">No programs found</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
