@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Programs</h1>
                    </div>
                    @if(Entrust::can('programs-manage'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('admin.programs.create', array()) }}" class="btn btn-success" disabled="disabled">New Program</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Wordpress Blogs (programs)</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td></td>
                            <td><strong>domain</strong></td>
                            <td><strong>id</strong></td>
                            <td><strong>registered</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $wpBlogs as $wpBlog )
                            <tr>
                                <td><a href="{{ URL::route('admin.programs.show', array('id' => $wpBlog->blog_id)) }}" class="btn btn-primary">Details</a></td>
                                <td><strong>{{ $wpBlog->domain }}</strong></td>
                                <td>{{ $wpBlog->blog_id }}</td>
                                <td>{{ $wpBlog->registered }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
