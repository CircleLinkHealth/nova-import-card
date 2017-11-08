@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/admin/questionSets.js') }}"></script>
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
                        <h1>Question Sets</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.questionSets.create', array()) }}" class="btn btn-success">New Question Set</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Question Sets</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <div class="row">
                            <div class="col-sm-12">
                                {!! Form::open(array('url' => URL::route('admin.questionSets.index', array()), 'class' => 'form-horizontal', 'id' => 'filterForm')) !!}
                                <div id="filters">
                                    <h3>Filter</h3>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2 text-right">{!! Form::label('filterProgram', 'Practice:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('filterProgram', array('all' => 'All Programs') + $programs, $filterProgram, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                            <div class="col-xs-2">{!! Form::label('filterQsType', 'Type:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('filterQsType', array('all' => 'All Qs Types') + $qsTypes, $filterQsType, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-xs-2 text-right">{!! Form::label('filterQuestion', 'Question:') !!}</div>
                                            <div class="col-xs-4">{!! Form::select('filterQuestion', array('all' => 'All Questions') + $questions, $filterQuestion, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top:50px;">
                                        <div class="col-sm-12">
                                            <div class="" style="text-align:center;">
                                                {!! Form::hidden('action', 'filter') !!}
                                                {!! Form::submit('Apply Filters', array('class' => 'btn btn-orange', 'id' => 'filterSubmit')) !!}
                                                {!! Form::submit('Reset Filters', array('class' => 'btn btn-orange')) !!}
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>

                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>provider_id</strong></td>
                                <td><strong>qs_type</strong></td>
                                <td><strong>qs_sort</strong></td>
                                <td><strong>qid</strong></td>
                                <td><strong>answer_response</strong></td>
                                <td><strong>aid</strong></td>
                                <td><strong>low</strong></td>
                                <td><strong>high</strong></td>
                                <td><strong>action</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $questionSets as $questionSet )
                                <tr>
                                    <td><a href="{{ URL::route('admin.questionSets.show', array('id' => $questionSet->qsid)) }}" class="btn btn-primary">Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $questionSet->provider_id }}</div></td>
                                    <td>{{ $questionSet->qs_type }}</td>
                                    <td>{{ $questionSet->qs_sort }}</td>
                                    <td>
                                        @if($questionSet->question)
                                            {{ $questionSet->question->msg_id }}
                                        @endif
                                    </td>
                                    <td>{{ $questionSet->answer_response }}</td>
                                    <td>{{ $questionSet->aid }}</td>
                                    <td>{{ $questionSet->low }}</td>
                                    <td>{{ $questionSet->high }}</td>
                                    <td>{{ $questionSet->action }}</td>
                                    <td><a href="{{ URL::route('admin.questionSets.edit', array('id' => $questionSet->qsid)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.questionSets.destroy', array('id' => $questionSet->qsid)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $questionSets->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
