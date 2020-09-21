<p>Thank you for choosing CircleLink for your CCM program!</p>

<p>For paying CircleLink and billing the appropriate Medicare or Medicare Advantage plan, i) a CircleLink invoice for the most recent month can be downloaded <a href="{{$invoiceURL}}"> here</a> (Login required) and ii) a billing report can be downloaded <a href="{{$patientReportURL}}"> here</a>. (Login required)</p>

<p>Finally, we have some billing tips below. Please let us know if you need anything else!</p>

<p>
    Best,<br>
    CircleLink Team
</p>

<p>P.s. Chronic Care Management Billing Tips:<br>
<ul>
    <li><b>Date of Service:</b> We recommend the last business day of billing month
    <li><b>Billing Location:</b> Billing provider’s location. CMS: “where the billing practitioner would furnish a
        face-to-face
        office visit with the patient”
    <li><b>Include a Chronic Condition:</b> The <a href="{{$patientReportURL}}">billing report</a> has a
        condition for
        each patient that we recommend submitting with your claim to Medicare
    <li><b>Billing Provider:</b> Main provider of patient. Always use an MD if possible
</ul>
</p>

<p>P.p.s. Don’t know how to log in to careplanmanager.com? See below :) </p>

<p><i>Your username is your work email address, and you can obtain or reset password by clicking the circled “Lost/Need a password...?” in below screenshot:</i></p>

<p style="text-align: center"><img style="width: 200px" src="{{ $message->embed(asset('/img/forgot.png')) }}"></p>

<p>@include('sales.partials.footer')</p>
