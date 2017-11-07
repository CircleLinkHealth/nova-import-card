@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Question Set</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Question Set: {{ $questionSet->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row" style="margin:20px 0px;">
                            <strong>provider_id:</strong><br>
                            {{ $questionSet->provider_id }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>Question Type:</strong><br>
                            {{ $questionSet->qs_type }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>Question Sort:</strong><br>
                            <p>{{ $questionSet->qs_sort }}</p>
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>Question:</strong><br>
                            {{ $questionSet->qid }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>answer_response:</strong><br>
                            {{ $questionSet->answer_response }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>aid:</strong><br>
                            {{ $questionSet->aid }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>low:</strong><br>
                            {{ $questionSet->low }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>high:</strong><br>
                            {{ $questionSet->high }}
                        </div>
                        <div class="row" style="margin:20px 0px;">
                            <strong>action:</strong><br>
                            {{ $questionSet->action }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
