@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.2.6/d3.min.js"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Statistics</div>
                    <div class="panel-body">

                        <div class="someclass">
                            <h2>Create A Bar Chart With D3 JavaScript</h2>
                            <div id="bar-chart">

                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>

        var chartdata = [40, 60, 80, 100, 70, 120, 100, 60, 70, 150, 120, 140];

        //  the size of the overall svg element
        var height = 200,
                width = 720,

//  the width of each bar and the offset between each bar
                barWidth = 40,
                barOffset = 20;

    </script>
@stop