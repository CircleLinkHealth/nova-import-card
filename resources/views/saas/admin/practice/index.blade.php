@extends('partials.providerUI')

@section('title', 'Manage Practices')
@section('activity', 'Manage Practices')

@section('content')
    @push('styles')
        <style>
            .selected-practice-index-filter {
                color: #009dea;
                text-decoration: underline;
            }
        </style>
    @endpush

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
                    <div class="panel-heading">
                        Practice
                        <div class="pull-right">
                            <a class="{{$filter == 'all' ? 'selected-practice-index-filter' : ''}}"
                               href="{{route('saas-admin.practices.index',['filter' => 'all'])}}">All</a> /
                            <a class="{{$filter == 'active' ? 'selected-practice-index-filter' : ''}}"
                               href="{{route('saas-admin.practices.index',['filter' => 'active'])}}">Active</a> /
                            <a class="{{$filter == 'inactive' ? 'selected-practice-index-filter' : ''}}"
                               href="{{route('saas-admin.practices.index',['filter' => 'inactive'])}}">Inactive</a>
                        </div>
                    </div>
                    <div class="panel-body">
                        @include('core::partials.errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Created At</strong></td>
                                <td><strong></strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse( $practices as $practice )
                                <tr>
                                    <td>
                                        <a href="{{ route('provider.dashboard.manage.practice', ['practiceSlug' => $practice->name]) }}"
                                           class=""><strong>{{ $practice->display_name }}</strong></a></td>
                                    <td>{{ date('F d, Y g:i A', strtotime($practice->created_at)) }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('provider.dashboard.manage.notifications', ['practiceSlug' => $practice->name]) }}"
                                           class="btn btn-xs btn-success">
                                            Edit Settings / Add Staff
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No practices found</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
