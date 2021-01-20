@extends('cpm-admin::partials.adminUI')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('core::partials.errors.errors')
            </div>
        </div>
        <div class="row" style="margin-left: -52px; margin-right: -55px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Practice Enrollment KPIs
                            </div>
                            <div class="panel-body">
                                <practice-kpis></practice-kpis>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection