<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"
      xmlns="http://www.w3.org/1999/html">

<div class="page-header">
    <h1>{{$data['provider_name']}}
        <small><span style="color: #50b2e2"> CircleLink Health </span> Account Status Report
            <b>({{Carbon\Carbon::now()->toDayDateTimeString()}})</b></small>
    </h1>
</div>