@extends('partials.providerUI')

@section('title', 'CCDAs')
@section('activity', 'CCDAs')

@section('content')
    <div class="row">
        <div class="row">
            <div class="col-sm-8">
            </div>
            <div class="col-sm-4">
                <div class="pull-right" style="margin:20px;">
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default">
                    <div class="panel-heading">Create CarePlan(s) from CCDAs</div>

                    <div class="panel-body">
                        @include('partials.importerTrainer')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection