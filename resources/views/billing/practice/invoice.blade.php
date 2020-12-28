<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<div class="container">
    <div class="page-header">
        <h1><b>{{$practice->display_name}}</b>
            <small><span style="color: #4fb2e2;"> CircleLink Health Invoice </span><b>#{{$invoice_num}} </b>({{$month}})
            </small>
        </h1>
    </div>
    <div class="col-sm-12" style="padding-bottom: 25px;">
        <div class="row col-sm-6">
            <span>
                CircleLink Health LLC <br>
                290 Harbor Drive<br>
                Stamford, CT 06902<br>
                janstey@circlelinkhealth.com<br>
                (203) 858-7206<br>
                www.circlelinkhealth.com <br>
            </span>
        </div>
    </div>

    <div class="col-sm-12" style="padding-bottom: 10px;">

        <div class="row col-sm-6">
            <b>INVOICE TO:</b><br>
            <span>
                {{$bill_to}} <br>
                {{$clh_address['line1']}}<br>
                {{$clh_address['line2']}}<br>
            </span><br><br>

        </div>

    </div>

    <div class="col-sm-12" style="padding-bottom: 15px;">
        <span style="font-size: 17px;">
                <b>Invoice Date</b> {{$invoice_date}} <br>
            <b>Due By </b> {{$due_by}}

            </span>
    </div>

    <div class="col-sm-12 row">

        <table class="table table-bordered">

            <tr>

                <td><b>Activity </b></td>
                <td><b>QTY</b></td>
                <td><b>Rate</b></td>
                <td><b>Amount</b></td>

            </tr>

            <tr>

                <td>CCM Services (CPT 99490)</td>
                <td>{{$billable}}</td>
                <td>{{$rate}}</td>
                <td>${{$invoice_amount}}</td>

            </tr>

        </table>

    </div>

    <div class="col-sm-12 row" style="text-align: right; font-size: 25px;">
        BALANCE DUE: ${{$invoice_amount}}
    </div>


    <div class="col-sm-12 row">
        Thank you for your business<br/><br/>

        Stay on time and save admin time by automating payments via Electronic Funds Transfer or Credit Card
        Payment.<br/><br/>

        Call us on <span style="color: #4fb2e2;">203-858-7206</span> to set up. <br/><br/>
    </div>

</div>