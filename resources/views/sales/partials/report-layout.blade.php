<link href="{{mix('/css/bootstrap.min.css')}}" rel="stylesheet">

<div class="container">

    @yield('content')

    @if($data['isEmail'])

        @include('sales.partials.footer', ['data' => $data])

    @endif

</div>