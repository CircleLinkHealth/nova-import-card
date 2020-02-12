@extends('partials.adminUI')

@section('content')
    @isset($chart)
        <div class="container-fluid">
        <div class="row">
                <div class="col-md-12">
                    <h1 style="font-size: 6rem;">
                        <span style="color: rgb(41, 90, 146);">Ops</span><span class="font-weight-bold" style="color: rgb(200, 211, 224);">Chart</span>
                    </h1>
                </div>
                <div class="col-md-12">
                    {!! $chart->container() !!}
                </div>
                <div class="col-md-12">
                    <p class="text-right">
                        <small style="color: rgb(48, 175, 209);">* data obtained from Daily Ops Report.</small>
                    </p>
                </div>
        </div>
    </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
        {!! $chart->script() !!}
    @endisset
@endsection