@extends('partials.providerUI')

@section('title', 'All Patient Notes')

@section('content')

    <?php

    $webix = "data:" . json_encode(array_values($results)) . "";

    ?>
    <div class="row main-form-block" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row ">
                <div class="main-form-title col-lg-12">
                    All Patient Notes
                </div>

                <div class="col-sm-3">
                    <h4 class="time-report__month">{{$time_title}}</h4>
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.listing'), 'method' => 'GET', 'class' => 'form-horizontal', 'style' => 'margin-right: 10px')) !!}
                <div class="form-group  pull-right" style="margin-top:10px; ">
                    <i class="icon icon--date-time"></i>
                    <div class="inline-block">
                        <label for="selectMonth" class="sr-only">Select Month:</label>
                        <select name="selectMonth" id="selectMonth" class="selectpicker" data-width="200px"
                                data-size="10" style="display: none;">
                            <option value="">Select Month</option>
                            @for($i = 0; $i < count($months); $i++)
                                <option value="{{$i+1}}" @if($month_selected == $i+1 && $dateFilter) {{'selected'}} @endif>{{$months[$i]}}</option>
                            @endfor
                        </select>

                        <div class="inline-block">
                            <label for="selectYear" class="sr-only">Select Year:</label>
                            <select name="selectYear" id="selectYear" class="selectpicker" data-width="150px" style="display: none;">
                                <option value="">Select Year</option>
                            @foreach($years as $year)
                                    <option value="{{$year}}"
                                            @if($year_selected == $year && $dateFilter){{'selected'}}@endif>{{$year}}</option>
                                @endforeach
                            </select>
                        <button type="submit" value="search" name="find" id="find" class="btn btn-primary">Go</button>
                        {!! Form::close() !!}
                            @if($dateFilter)
                                <a href="{{ URL::route('patient.note.listing') }}" class="btn btn-primary">Reset</a>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="main-form-horizontal main-form-primary-horizontal col-md-12" style="border-top: 3px solid #50b2e2">
                    @if($notes)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div><br/>
                        <style>
                            .webix_hcell {
                                background-color: #d2e3ef;
                            }
                        </style>
                        <script>
                            function startCompare(value, filter) {
                                value = value.toString().toLowerCase();
                                filter = '<' + filter.toString().toLowerCase();
                                return value.indexOf(filter) === 0;
                            }
                            webix.locale.pager = {
                                first: "<<",// the first button
                                last: ">>",// the last button
                                next: ">",// the next button
                                prev: "<"// the previous button
                            };
                            webix.ui.datafilter.mySummColumn = webix.extend({
                                refresh: function (master, node, value) {
                                    var seconds = 0;
                                    master.data.each(function (obj) {
                                        seconds = seconds + parseInt(obj.duration);
                                    });
                                    var date = new Date(seconds * 1000);
                                    var mm = Math.floor(seconds / 60);
                                    var ss = date.getSeconds();
                                    if (ss < 10) {
                                        ss = "0" + ss;
                                    }
                                    var time = "" + mm + ":" + ss;
                                    result = "<span title='" + mm + ":" + ss + "' style='float:right;'><b>" + time + "</b></span>";
                                    node.firstChild.innerHTML = result;
                                }
                            }, webix.ui.datafilter.summColumn);

                            obs_alerts_dtable = new webix.ui({
                                container: "obs_alerts_container",
                                view: "datatable",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                scrollX: true,
                                resizeColumn: true,
                                tooltip:true,
                                footer: false,
                                columns: [

                                    {
                                        id: "patient_name",
                                        header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string',
                                        template:"<a href='<?php echo URL::route('patient.note.index', array('patientId' => '#patient_id#')); ?>'>#patient_name#</a>"

                                    },
                                    {
                                        id: "program_name",
                                        header: ["Program", {content: "selectFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string',
                                    },
                                    {
                                        id: "provider_name",
                                        header: ["Provider Name", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string'
                                    },
                                    {
                                        id: "author_name",
                                        header: ["Author", {content: "selectFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string'
                                    },
                                    {
                                        id: "tags",
                                        css: {'text-align': 'left', 'top': 0, 'left': 0, 'bottom': 0, 'right': 0},
                                        header: ["Status", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string'
                                    },
                                    {
                                        id: "type",
                                        header: ["Type", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string',
                                        template:"<a href='<?php echo URL::route('patient.note.view', array('patient' => '#patient_id#','noteId'=>'#id#')); ?>'>#type#</a>"
                                    },
                                    {
                                        id: "date",
                                        header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string'
                                    },
                                    {
                                        id: "comment",
                                        header: ["Preview", {content: "textFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string',
                                        tooltip:['#comment#']
                                    }
                                ],

                                ready: function () {
                                    this.adjustRowHeight("obs_key");
                                },

                                <?php echo $webix  ?>
                            });


                            obs_alerts_dtable.hideColumn("program_name");

                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            });

                        </script>
                        <div class="row">
                            <div class="col-sm-6">
                                <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:4px;'
                                       onclick="webix.toPDF(obs_alerts_dtable);">
                                <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:4px;'
                                       onclick="webix.toExcel(obs_alerts_dtable);">
                                @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                                    <input id='site_show_btn' type='button' class='btn btn-primary' value='Show Program' style='margin:4px;' onclick='obs_alerts_dtable.showColumn("program_name");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
                                    <input id='site_hide_btn' type='button' class='btn btn-primary' value='Hide Program' style='display:none;margin:4px;' onclick='obs_alerts_dtable.hideColumn("program_name");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
                                @endif
                            </div>
                            <div class="col-sm-6 vertical-center" style="padding: 10px; top: -14px">
                                <p style="text-align: center; margin-bottom: 0px"><strong><em>Legend</em></strong></p>
                                <div class="label label-info" style="margin-right: 4px; text-align: right;">
                                    <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                                </div>
                                Patient Reached

                                <div class="label label-danger" style="margin-right: 4px; text-align: right;">
                                    <span class="glyphicon glyphicon-flag"></span>
                                </div>
                                Patient in ER

                                <div class="label label-warning" style="margin-right: 4px; text-align: right;">
                                    <span class="glyphicon glyphicon-envelope"></span>
                                </div>
                                Forwarded To Provider
                            </div>
                        </div>
                </div>
                @else
                    <div style="text-align:center;margin:50px;">There are no patients notes to view.
                    </div>
                @endif
                <div id="rohstar" style="color: #00ACC1">

{{--                    {!! $results->render()  !!}--}}
                </div>
        </div>
    </div>
    @stop