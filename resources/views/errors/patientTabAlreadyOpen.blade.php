<html>
<head>
    <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>

    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #000;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            margin: 0;
            padding: 0;
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 32px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="title">Seems like you have open sessions (tabs) for different patients. <br>
            You can only work on one patient at a time. <br>
            Please <a
                    href="{{ empty($patientId) ? URL::route('patients.search') : URL::route('patient.summary', array('patient' => $patientId)) }}" target="_blank">go to
                the open session</a> close that window, and then refresh this page to resume your work.
        </div>

        <a class="btn btn-primary" href="{{ URL::previous() }}"
           style="text-decoration:none;font-weight:bold;">Go back</a> or

        <a class="btn btn-primary" href="{{ URL::route('patients.dashboard', array()) }}"
           style="text-decoration:none;font-weight:bold;">Go to Dashboard</a>


    </div>
</div>
</body>
</html>
