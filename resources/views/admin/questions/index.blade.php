@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/admin/questions.js') }}"></script>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Questions</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.questions.create', array()) }}" class="btn btn-success">New Question</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Questions</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>msg_id</strong></td>
                                <td><strong>qtype</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>icon</strong></td>
                                <td><strong>category</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $questions as $question )
                                <tr>
                                    <td><a href="{{ URL::route('admin.questions.show', array('id' => $question->qid)) }}" class="btn btn-primary">Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $question->msg_id }}</div></td>
                                    <td>{{ $question->qtype }}</td>
                                    <td>{{ $question->obs_key }}</td>
                                    <td>{!! $question->iconHtml() !!}</td>
                                    <td>{{ $question->category }}</td>
                                    <td><a href="{{ URL::route('admin.questions.edit', array('id' => $question->qid)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.questions.destroy', array('id' => $question->qid)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $questions->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
