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
            color: rgb(88, 89, 90);
        }

        hr {
            margin: 0;
            padding: 0;
            border: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
    </style>

    <script>
        function subst() {
            //http://biostall.com/wkhtmltopdf-add-header-footer-to-only-first-last-page/
            var vars = {};

            // explode the URL query string
            var x = document.location.search.substring(1).split('&');

            // loop through each query string segment and get keys/values
            for (var i in x)
            {
                var z = x[i].split('=',2);
                vars[z[0]] = unescape(z[1]);
            }

            // an array of all the parameters passed into the footer file
            var x = ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection'];

            // each page will have an element with class 'section' from the body of the footer HTML
            var y = document.getElementsByClassName('report-header');
            for(var j = 0; j < y.length; j++)
            {
                // if current page equals total pages
                // if (vars[x[2]] == vars[x[1]])

                //if current page is the first
                if (vars[x[2]] == 1)
                {
                    y[j].style.display = "none";
                }
            }
        }
    </script>
</head>
<body onload="subst()">
<div class="report-header">
    Wellness Visit {{$reportName}} for&nbsp;<span class="patient-name"><strong>{{$patientName}}</strong></span>&nbsp;(DOB: {{$patientDob}})
    <hr/>
</div>
</body>
</html>
