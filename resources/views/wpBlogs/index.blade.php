@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Wordpress Blogs (programs)</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>domain</strong></td>
                            <td><strong>registered</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $wpBlogs as $wpBlog )
                            <tr>
                                <td>{{ $wpBlog->blog_id }}</td>
                                <td>{{ $wpBlog->domain }}</td>
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
