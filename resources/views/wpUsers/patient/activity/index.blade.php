@extends('partials.providerUI')

@section('title', 'Patient Activity Report')
@section('activity', 'Patient Activity Report')

@section('content')
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title">
                    Patient Activity Report
                </div>
                @include('partials.userheader')
                <div class="col-sm-3">
                    <h4 class="time-report__month">{{$month_selected_text}} {{$year_selected}}</h4>
                </div>
                {!! Form::open(array('url' => URL::route('patient.activity.providerUIIndex', ['patientId' => $patient]), 'method' => 'GET', 'class' => 'form-horizontal', 'style' => 'margin-right: 10px')) !!}
                <div class="form-group  pull-right" style="margin-top:10px; ">
                    <i class="icon icon--date-time"></i>
                    <div class="inline-block">
                        <label for="selectMonth" class="sr-only">Select Month:</label>
                        <select name="selectMonth" id="selectMonth" class="selectpicker" data-width="200px"
                                data-size="10" style="display: none;">
                            <option value="">Select Month</option>
                            @for($i = 0; $i < count($months); $i++)
                                <option value="{{$i+1}}" @if($month_selected == $i+1) {{'selected'}} @endif>{{$months[$i]}}</option>
                            @endfor

                        </select>

                        <div class="inline-block">
                            <label for="selectYear" class="sr-only">Select Year:</label>
                            <select name="selectYear" id="selectYear" class="selectpicker" data-width="100px"
                                    data-size="10" style="display: none;">
                                @foreach($years as $year)
                                    <option value="{{$year}}" @if($year_selected == $year) {{'selected'}} @endif>{{$year}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" value="Search" name="find" id="find" class="btn btn-primary">Go</button>
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12"
                     style="border-top: 3px solid #50b2e2">
                    @if($data)
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
                                //css:"webix_clh_cf_style",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                // leftSplit:2,
                                scrollX: false,
                                resizeColumn: true,
                                footer: true,
                                columns: [


                                    {
                                        id: "performed_at",
                                        header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                        footer: {text: "Total Time for the Month (Min:Sec):", colspan: 3},
                                        width: 180,
                                        sort: 'string'
                                    },
                                    {
                                        id: "type",
                                        header: ["Activity", {content: "textFilter", placeholder: "Filter"}],

                                        template: function (obj) {
                                            if (obj.logged_from == "manual_input" || obj.logged_from == "activity")
                                                return "<a href='<?php echo URL::route('patient.activity.view', array(
                                                                'patientId' => $patient->id,
                                                                'atcId'     => ''
                                                        )); ?>/" + obj.id + "'>" + obj.type + "</a>";
                                            else
                                                return obj.type;
                                        },

                                        fillspace: true,
                                        width: 200,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "left"}
                                    },

                                    {
                                        id: "provider_name",
                                        header: ["Provider", {content: "textFilter", placeholder: "Filter"}],
                                        width: 200,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "right"}
                                    },
                                    {
                                        id: "duration",
                                        header: ["Total", "(Min:Sec)"],
                                        width: 100,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "right"},
                                        footer: {content: "mySummColumn"},
                                        template: function (obj) {
                                            var seconds = obj.duration;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            if (ss < 10) {
                                                ss = "0" + ss;
                                            }
                                            var time = mm + ":" + ss;
                                            return "<span title=':" + mm + ":" + ss + "' style='float:right;'>" + time + "</span>";
                                        }
                                    }
                                ],
                                ready: function () {
                                    this.adjustRowHeight("obs_key");
                                },
                                /*ready:function(){
                                 this.adjustRowHeight("obs_value");
                                 },*/
                                pager: {
                                    animate: true,
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 10, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },
                                {!! $activity_json !!}                         });
                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            })
                        </script>
                        @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))
                            <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toPDF($$(obs_alerts_dtable), {
                                           header:'CarePlanManager.com - Patient Activity Report <?= date('M d,Y') ?>',
                                           orientation:'landscape',
                                           autowidth:true,
                                           columns:{
                                           'performed_at':       { header:'Date', width: 200, template: webix.template('#performed_at#') },
                                           'type':             { header:'Activity',    width:150, sort:'string', template: webix.template('#type#')},
                                           'provider_name':    { header:'Provider',    width:200, sort:'string', template: webix.template('#provider_name#') },
                                           'duration':  { header: 'Total (Min:Sec)', width: 70, sort: 'string',
                                           template: function (obj) {
                                           var seconds = obj.duration;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           if (ss < 10) {ss = '0'+ss;}
                                           var time = mm+':'+ss;
                                           return mm+':'+ss;
                                           }
                                           }
                                           }
                                           });">
                            <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toExcel(obs_alerts_dtable);">
                        @endif
                    @else
                        <div style="text-align:center;margin:50px;">There are no patient activities to display for this
                            month.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@stop