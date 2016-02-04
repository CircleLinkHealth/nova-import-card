@extends('partials.providerUI')

@section('content')
    <div class="row" style="margin-top:60px;">
        <script>
            $(document).ready(function () {
                $(".chartDiv").hide();

                //$(".selectpicker").selectpicker();

                $('.submit-chart-select').on('click', function () {
                    console.log($("#select_chart_type").val());
                    $("#select_chart_type").click();
                });

                $('#select_chart_type').on('change click', function () {
                    console.log($("#select_chart_type").val());
                    chartTypeId = "#chartDiv" + $(this).val();
                    $(".chartDiv").hide();
                    $(chartTypeId).show();
                    return false;
                });

                $('#select_chart_type').click();

            });
        </script>
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Graph
                </div>
                @include('partials.userheader')
                <div class="row">
                    <div class="col-sm-12" style="margin-top:10px;">
                        <div class="col-xs-4 col-sm-4">
                            <div class="form-group">
                                <label for="select_chart_type">
                                    Graph:
                                </label>
                                <select name="select_chart_type" id="select_chart_type" class="selectpicker"
                                        data-width="160px" data-size="10">
                                    <option value="Blood_Sugar">Blood Sugar</option>
                                    <option value="Blood_Pressure">Blood Pressure</option>
                                    <option value="Weight">Weight</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4">
                            <div class="form-group hidden">
                                <label for="select_chart_date">
                                    Time:
                                </label>
                                <select name="select_chart_date" id="select_chart_date" class="selectpicker"
                                        data-width="160px" data-size="10">
                                    <option value="Last 7 Days">Last 7 Days</option>
                                    <option value="Last Month">Last Month</option>
                                    <option value="YTD">YTD</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-lg-offset-1">
                            <div class="row">
                                <div class="col-xs-6">
                                            <span class="text-right"><a
                                                        class="btn btn-green btn-sm submit-chart-select">Go</a></span>
                                </div>
                                <div class="col-xs-6">
                                            <span class="text-right"><a class="btn btn-green btn-sm submit-chart-select"
                                                                        href="/manage-patients/patient-summary/?user=308&detail=obs_biometrics"><<
                                                    Return</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="col-xs-12 col-sm-12" style="border-bottom: 3px solid #50b2e2;">
                            @if($biometrics_array['Blood_Sugar']['data'] != '')
                                <div id="chartDivBlood_Sugar" class="chartDiv"
                                     style="width:600px;height:300px;margin:10px auto;border:0px solid red"></div>
                                <script src="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.js"
                                        type="text/javascript"></script>
                                <link rel="stylesheet"
                                      href="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.css"
                                      type="text/css">
                                <script>
                                    webix.ui({
                                        view: "chart",
                                        container: "chartDivBlood_Sugar",
                                        type: "line",
                                        value: "#Reading#",
                                        radius: 0,
                                        border: false,
                                        preset: 'simple',
                                        xAxis: {
                                            template: "#Week#",
                                            step: 2,
                                            title: "Week",
                                        },
                                        yAxis: {
                                            start: 40, end: 170, step: 20,                                                // title: "Reading",
                                            template: function (obj) {
                                                return (obj % 10 ? "" : obj)
                                            }
                                        },
                                        tooltip: {
                                            template: "#Reading#"
                                        },
                                        eventRadius: 10,
                                        data: [
                                            {!! $biometrics_array['Blood_Sugar']['data'] !!}
                                        ]
                                    });
                                </script>
                            @else
                                <div id="chartDivBlood_Sugar" class="chartDiv"
                                     style="width:600px;height:300px;margin:10px auto;border:0px solid red">
                                    <div style="text-align:center;margin:50px;">There is no data to display</div>
                                </div>
                        </div>@endif
                        @if($biometrics_array['Blood_Pressure']['data'] != '')
                            <div id="chartDivBlood_Pressure" class="chartDiv"
                                 style="width:600px;height:300px;margin:10px auto;border:0px solid red"></div>
                            <script src="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.js"
                                    type="text/javascript"></script>
                            <link rel="stylesheet"
                                  href="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.css"
                                  type="text/css">
                            <script>
                                webix.ui({
                                    view: "chart",
                                    container: "chartDivBlood_Pressure",
                                    type: "line",
                                    value: "#Reading#",
                                    radius: 0,
                                    border: false,
                                    preset: 'simple',
                                    xAxis: {
                                        template: "#Week#",
                                        step: 2,
                                        title: "Week",
                                    },
                                    yAxis: {
                                        start: 80, end: 160, step: 10,                                                // title: "Reading",
                                        template: function (obj) {
                                            return (obj % 10 ? "" : obj)
                                        }
                                    },
                                    tooltip: {
                                        template: "#Reading#"
                                    },
                                    eventRadius: 10,
                                    data: [

                                        {!! $biometrics_array['Blood_Pressure']['data'] !!}
                                     ]
                                });
                            </script>@else
                            <div id="chartDivBlood_Pressure" class="chartDiv"
                                 style="width:600px;height:300px;margin:10px auto;border:0px solid red">
                                <div style="text-align:center;margin:50px;">There is no data to display</div>
                            </div>
                    </div>@endif
                    @if($biometrics_array['Weight']['data'] != '')
                        <div id="chartDivWeight" class="chartDiv"
                             style="width:600px;height:300px;margin:10px auto;border:0px solid red"></div>
                        <script src="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.js"
                                type="text/javascript"></script>
                        <link rel="stylesheet"
                              href="http://testcrisfield.careplanmanager.com/wp-content/themes/CLH_Provider/respo/webix/codebase/webix.css"
                              type="text/css">
                        <script>
                            webix.ui({
                                view: "chart",
                                container: "chartDivWeight",
                                type: "line",
                                value: "#Reading#",
                                radius: 0,
                                border: false,
                                preset: 'simple',
                                xAxis: {
                                    template: "#Week#",
                                    step: 2,
                                    title: "Week",
                                },
                                yAxis: {
                                    start: 80, end: 220, step: 30,                                                // title: "Reading",
                                    template: function (obj) {
                                        return (obj % 10 ? "" : obj)
                                    }
                                },
                                tooltip: {
                                    template: "#Reading#"
                                },
                                eventRadius: 10,
                                data: [

                                    {!! $biometrics_array['Weight']['data'] !!}
                                    ]
                            });
                        </script>@else
                        <div id="chartDivWeight" class="chartDiv"
                             style="width:600px;height:300px;margin:10px auto;border:0px solid red">
                            <div style="text-align:center;margin:50px;">There is no data to display</div>
                        </div>
                </div>@endif
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
    </div>
@stop