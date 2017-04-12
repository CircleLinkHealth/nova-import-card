<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="page-header">
    <h1>CircleLink Health
        <small>Invoice for <b>{{$practice->display_name}}</b> ({{$month}})</small>
    </h1>
</div>

<ul>
    <li>Invoice #:  ${{$invoice_num}}/patient</li>
    <li>Rate: ${{$rate}}/patient</li>
    <li>Billable this month: {{$billable}}</li>
    <li>Invoice Amount: ${{$invoice_amount}}.00</li>
</ul>

<br />

Thank you for your business.<br />

Stay on time and save admin time by automating payments via Electronic Funds Transfer or Credit Card Payment.<br />

Call us on 203-858-7206 to set up. <br />