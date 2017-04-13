@extends('partials.adminUI')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Practice</h1>
                    </div>
                    @if(Entrust::can('programs-manage'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('admin.programs.create', array()) }}" class="btn btn-success">New
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
                            @if (count($wpBlogs) > 0)
                                @foreach( $wpBlogs as $wpBlog )
                                    <tr>
                                        <td>
                                            <a href="{{ URL::route('admin.programs.show', array('id' => $wpBlog->id)) }}"
                                               class=""><strong>{{ $wpBlog->display_name }}</strong></a></td>
                                        <td>
                                            @if (count($wpBlog->users) > 0)
                                                <a href="{{ URL::route('admin.users.index', array('filterProgram' => $wpBlog->id)) }}"
                                                   class=""><strong>{{ count($wpBlog->users()->whereHas('roles', function ($q) {
					$q->where('name', '=', 'participant');
				})->get()) }}</strong></a>
                                            @endif
                                        </td>
                                        <td>{{ date('F d, Y g:i A', strtotime($wpBlog->created_at)) }}</td>
                                        <td class="text-right">
                                            @if(Entrust::can('programs-manage'))
                                                <a href="{{ URL::route('provider.dashboard.index', ['practiceSlug' => $wpBlog->name]) }}"
                                                   class="btn btn-xs btn-success">
                                                    Admin
                                                </a>

                                                <a href="{{ URL::route('admin.programs.edit', array('id' => $wpBlog->id)) }}"
                                                   class="btn btn-xs btn-info">Edit
                                                </a>

                                                <a href="{{ URL::route('admin.programs.destroy', array('id' => $wpBlog->id)) }}"
                                                   class="btn btn-xs btn btn-warning"
                                                   style="margin-left:10px;">Remove
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
@stop
