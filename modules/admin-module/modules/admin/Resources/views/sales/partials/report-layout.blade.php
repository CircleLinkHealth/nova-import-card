<link href="{{asset('/css/bootstrap.min.css')}}" rel="stylesheet">

<div class="container">

    @yield('content')

    @if($data['isEmail'])

        @include('cpm-admin::sales.partials.footer', ['data' => $data])

    @endif

</div>