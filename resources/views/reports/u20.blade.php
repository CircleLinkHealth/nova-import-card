@extends('partials.providerUI')
@section('content')

    <div class="row main-form-block" style="margin-top:60px;">
        <div class="main-form-container  col-lg-8 col-lg-offset-2">
            <div class="row ">
                <div class="main-form-title col-lg-12">
                    Under 20 Minute Report
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">

                    {!! Form::open(array('url' => route('patient.reports.u20'), 'method' => 'GET', 'class' => 'form-horizontal')) !!}
                    <div class="col-sm-3">
                        <h4 class="time-report__month">{{$month_selected_text}} {{$year_selected}}</h4>
                    </div>
                    <div class="form-group  pull-right" style="margin-top:10px;">
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
                            <button type="submit" value="Search" name="find" id="find" class="btn btn-primary">Go
                            </button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
                <div class="main-form-horizontal main-form-primary-horizontal col-md-12">
                    @if($data)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div><br/>

                        @push('styles')
                        <style>
                            .webix_hcell {
                                background-color: #d2e3ef;
                            }
                        </style>
                        @endpush

                        @push('scripts')
                        <script>
                            function filterText(text) {
                                // var text = node;
                                if (!text) return obs_alerts_dtable.filter();

                                obs_alerts_dtable.filter(function (obj) {
                                    return obj.ccm_status == text;
                                })
                            }

                            function sortByParam(a, b) {
                                a = a.patient_name_sort;
                                b = b.patient_name_sort;
                                return a > b ? 1 : (a < b ? -1 : 0);
                            }

                            function zeroPad(nr, base) {
                                var len = (String(base).length - String(nr).length) + 1;
                                return len > 0 ? new Array(len).join('0') + nr : nr;
                            }

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
                            obs_alerts_dtable = new webix.ui({
                                container: "obs_alerts_container",
                                view: "treetable",
                                // view:"datatable",
                                //css:"webix_clh_cf_style",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                // leftSplit:2,
                                scrollX: false,
                                resizeColumn: true,
                                columns: [
                                    {
                                        id: "patient_name",
                                        header: ["Patient", {content: "textFilter", placeholder: "Filter"}],
                                        width: 200,
                                        sort: 'string',
                                        template: "<a href='<?php echo route('patient.activity.providerUIIndex',
                                            array('patient' => '#patient_id#')); ?>'>#patient_name#</a>"

                                    },
                                    {
                                        id: "site",
                                        header: ["Practice", {content: "selectFilter"}],
                                        width: 150,
                                        sort: 'string'
                                    },
                                    {
                                        id: "ccm_status",
                                        header: ["CCM Status", {content: "selectFilter", placeholder: "Filter"}],
                                        width: 110,
                                        sort: 'string'
                                    },
                                    {
                                        id: "dob",
                                        header: ["DOB", {content: "textFilter", placeholder: "Filter"}],
                                        width: 110,
                                        sort: 'string'
                                    },
                                    {
                                        id: "colsum_careplan",
                                        header: ["CarePlan", "(Min:Sec)"],
                                        width: 80,
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"},
                                        template: function (obj) {
                                            var seconds = obj.colsum_careplan;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                        }
                                    },
                                    {
                                        id: "colsum_progress",
                                        header: ["Progress", "(Min:Sec)"],
                                        width: 80,
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"},
                                        template: function (obj) {
                                            var seconds = obj.colsum_progress;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                        }
                                    },
                                    {
                                        id: "colsum_rpm",
                                        header: ["RPM", "(Min:Sec)"],
                                        width: 80,
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"},
                                        template: function (obj) {
                                            var seconds = obj.colsum_rpm;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                        }
                                    },
                                    {
                                        id: "colsum_tcc",
                                        header: ["CC", "(Min:Sec)"],
                                        width: 80,
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"},
                                        format: webix.numberFormat,
                                        template: function (obj) {
                                            var seconds = obj.colsum_tcc;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                        }
                                    },
                                    {
                                        id: "colsum_other",
                                        header: ["Other", "(Min:Sec)"],
                                        width: 80,
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"}
                                        , template: function (obj) {
                                        var seconds = obj.colsum_other;
                                        var date = new Date(seconds * 1000);
                                        var mm = Math.floor(seconds / 60);
                                        var ss = date.getSeconds();
                                        console.log("This: " + obj);
                                        return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                    }
                                    },
                                    {
                                        id: "colsum_total",
                                        header: ["Total", "(Min:Sec)"],
                                        sort: 'int',
                                        css: {"color": "black", "text-align": "right"},
                                        format: webix.numberFormat,
                                        template: function (obj, common) {
                                            var seconds = obj.colsum_total;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
                                        }
                                    }
                                ],
                                pager: {
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 10, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },
                                {!! $activity_json !!}
                            });

                            function gby() {
                                obs_alerts_dtable.ungroup();
                                obs_alerts_dtable.group({
                                    by: "provider",
                                    map: {
                                        colsum_total: ["colsum_total", "sum"],
                                        title: ["provider"]
                                    },
                                    footer: {
                                        colsum_total: ["colsum_total", "sum"],
                                        row: function (obj) {
                                            var seconds = obj.colsum_total;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            var time = mm + ":" + zeroPad(ss, 10);
                                            return "<span style='float:right;'>Total Time: " + time + "</span>";
                                        }
                                    },
                                    row: "provider"
                                });
                            }

                            function gbyp() {
                                obs_alerts_dtable.ungroup();
                                obs_alerts_dtable.group({
                                    by: "patient_name",
                                    map: {
                                        colsum_total: ["colsum_total", "sum"],
                                        title: ["patient_name"]
                                    },
                                    footer: {
                                        colsum_total: ["colsum_total", "sum"],
                                        row: function (obj) {
                                            var seconds = obj.colsum_total;
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            if (ss < 10) {
                                                ss = "0" + ss;
                                            }
                                            var time = mm + ":" + ss;
                                            return "<span style='float:right;'>Total Time: " + time + "</span>";
                                        }
                                    },
                                    row: "patient_name"
                                });
                            }

                            function ug() {
                                obs_alerts_dtable.ungroup();
                            }

                            obs_alerts_dtable.ungroup();
                            obs_alerts_dtable.sort('#patient_name#');
                            obs_alerts_dtable.hideColumn("site");

                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            })
                        </script>
                        @endpush

                        @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))
                            <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toPDF($$(obs_alerts_dtable), {
                                           header:'CarePlanManager.com - Patients Under 20 Minutes CCM Time <?= date('M d,Y') ?>',
                                           orientation:'landscape',
                                           autowidth:true,
                                           columns:{
                                           // 'provider_name':    { header:'Provider',    width:200, sort:'string', template: webix.template('#provider_name#') },
                                           'patient_name':       { header:'Patient Name', width: 200, template: webix.template('#patient_name#') },
                                           'site':             { header:'Program',    width:150, sort:'string', template: webix.template('#site#')},
                                           'dob':              { header:'DOB',    width:100, sort:'string', template: webix.template('#dob#')},

                                           'colsum_careplan':  { header: 'CarePlan (Min:Sec)', width: 70, sort: 'int',
                                           template:function (obj) {
                                           var seconds = obj.colsum_careplan;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           },
                                           'colsum_progress':  { header: 'Progress (Min:Sec)', width: 70, sort: 'int',
                                           template:function (obj) {
                                           var seconds = obj.colsum_progress;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           },
                                           'colsum_rpm':  { header: 'RPM (Min:Sec)', width: 70, sort: 'int',
                                           template:function (obj) {
                                           var seconds = obj.colsum_rpm;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           },
                                           'colsum_tcc':  { header: 'CC (Min:Sec)', width: 50, sort: 'int',
                                           format: webix.numberFormat,
                                           template:function (obj) {
                                           var seconds = obj.colsum_tcc;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           },
                                           'colsum_other':  { header: 'Other (Min:Sec)', width: 70, sort: 'int',
                                           template:function (obj) {
                                           var seconds = obj.colsum_other;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           },
                                           'colsum_total':  { header: 'Total (Min:Sec)', width: 70, sort: 'int',
                                           format: webix.numberFormat,
                                           template: function (obj, common) {
                                           var seconds = obj.colsum_total;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           return mm + ':' + zeroPad(ss,10);
                                           }
                                           }}
                                           });">
                            <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toExcel(obs_alerts_dtable);">
                        @endif
                        @if ( !Auth::guest() && Auth::user()->hasPermission(['admin-access']))
                            <input id='site_show_btn' type='button' class='btn btn-primary' value='Show Practice'
                                   style='margin:15px;'
                                   onclick='obs_alerts_dtable.showColumn("site");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
                            <input id='site_hide_btn' type='button' class='btn btn-primary' value='Hide Practice'
                                   style='display:none;margin:15px;'
                                   onclick='obs_alerts_dtable.hideColumn("site");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
                        @endif
                        @push('scripts')
                        <script type="text/javascript">
                            window.onload = filterText('Enrolled');
                        </script>
                        @endpush

                    @else
                        <div style="text-align:center;margin:50px;">There are no patients under 20 minutes this month.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@stop