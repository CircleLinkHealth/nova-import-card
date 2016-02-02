@extends('partials.adminUI')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-2">
                        <h1>Programs</h1>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Programs</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>Name</strong></td>
                                <td><strong>Users</strong></td>
                                <td><strong>Created</strong></td>
                                <td><strong></strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($wpBlogs) > 0)
                                @foreach( $wpBlogs as $wpBlog )
                                    <tr>
                                        <td><a href="{{ URL::route('admin.programs.show', array('id' => $wpBlog->blog_id)) }}" class=""><strong>{{ $wpBlog->display_name }}</strong></a></td>
                                        <td>
                                            @if (count($wpBlogs) > 0)
                                                <a href="{{ URL::route('admin.users.index', array('filterProgram' => $wpBlog->blog_id)) }}" class=""><strong>{{ count($wpBlog->users) }}</strong></a>
                                            @endif
                                        </td>
                                        <td>{{ date('F d, Y g:i A', strtotime($wpBlog->created_at)) }}</td>
                                        <td class="text-right">
                                            @if(Entrust::can('programs-manage'))
                                                <a href="{{ URL::route('admin.programs.edit', array('id' => $wpBlog->blog_id)) }}" class="btn btn-xs btn-info">Edit</a><a href="{{ URL::route('admin.programs.destroy', array('id' => $wpBlog->blog_id)) }}" class="btn btn-xs btn btn-warning" style="margin-left:10px;">Remove</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6">No programs found</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
