<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"
      xmlns="http://www.w3.org/1999/html">
<div class="container">
    <div class="page-header">
        <h1>{{$data['name']}}
            <small><br/>
                <b><span style="color: #50b2e2"> CircleLink Health </span>Sales Report - {{$data['end']}}</b></small>
        </h1>
    </div>

@yield('content')

@include('sales.partials.footer', ['data' => $data])

</div>