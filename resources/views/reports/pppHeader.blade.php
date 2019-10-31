<!doctype html>
<html>
<head>
    <title>test</title>

    <link href="{{ asset('css/pdf.css') }}" rel="stylesheet">

    <style>
        .patient-name {
            color: #50b2e2;
            font-size: 20px;
            font-weight: 600;
        }

        .report-header {
            margin: 33px 25px 3px;
            font-family: Poppins, sans-serif;
            font-size: 14px;
            font-weight: normal;
            font-stretch: normal;
            font-style: normal;
            line-height: normal;
            letter-spacing: 0.78px;
            color: rgba(26, 26, 26, 0.25);
            border-bottom: 1px solid rgba(0, 0, 0, .1);
        }
    </style>
</head>
<body>
<div class="report-header">
    Wellness Visit PPP for&nbsp;<span class="patient-name">{{$patientName}}</span>&nbsp;(DOB: {{$patientDob}})
</div>
</body>
</html>
