@extends('partials.providerUI')
{{$notes}}
@section('content')

    <div class="row main-form-block" style="margin-top:60px;">
        <div class="main-form-container  col-lg-8 col-lg-offset-2">
            <div class="row ">
                <div class="main-form-title col-lg-12">
                    All Patient Notes
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">

                    {!! Form::open(array('url' => URL::route('patient.reports.u20'), 'method' => 'GET', 'class' => 'form-horizontal')) !!}

                    <div class="form-group  pull-right" style="margin-top:10px;">
                        <i class="icon icon--date-time"></i>
                </div>
                <div class="main-form-horizontal main-form-primary-horizontal col-md-12">
                    @if($notes)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div><br/>
                        <style>
                            .webix_hcell {
                                background-color: #d2e3ef;
                            }
                        </style>
                        <script>
                            function filterText(text){
                                // var text = node;
                                if (!text) return obs_alerts_dtable.filter();

                                obs_alerts_dtable.filter(function(obj){
                                    return obj.ccm_status == text;
                                })
                            }
                            function sortByParam(a,b){
                                a = a.patient_name_sort;
                                b = b.patient_name_sort;
                                return a>b?1:(a<b?-1:0);
                            }
                            function zeroPad(nr,base){
                                var  len = (String(base).length - String(nr).length)+1;
                                return len > 0? new Array(len).join('0')+nr : nr;
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
                                view: "datatable",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                scrollX: false,
                                resizeColumn: true,
                                columns: [
                                    {
                                        id: "patient_name",
                                        header: ["Patient", {content: "textFilter", placeholder: "Filter"}],
                                        width: 200,
                                        sort:'string',

                                    },                                    {
                                        id: "program_name",
                                        header: ["Patient", {content: "textFilter", placeholder: "Filter"}],
                                        // fillspace: true,
                                        width: 200,
                                        sort:'string',

                                    },                                    {
                                        id: "provider_name",
                                        header: ["Patient", {content: "textFilter", placeholder: "Filter"}],
                                        // fillspace: true,
                                        width: 200,
                                        sort:'string',

                                    }
                                ],

                                ready: function () {
                                    this.adjustRowHeight("obs_key");
                                },

                                pager: {
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 10, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },

                            {!! $notes !!}

                            });

                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            })
                        </script>
                        <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                               onclick="webix.toPDF($$(obs_alerts_dtable), {
                                       header:'CarePlanManager.com - Patients Under 20 Minutes CCM Time <?= date('M d,Y') ?>',
                                       orientation:'landscape',
                                       autowidth:true,
                                       columns:{
                                       // 'provider_name':    { header:'Provider',    width:200, sort:'string', template: webix.template('#provider_name#') },
                                       'patient_name':       { header:'Patient Name', width: 200, template: webix.template('#patient_name#') }

                                       });">
                        <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                               onclick="webix.toExcel(obs_alerts_dtable);">
                        @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                            <input id='site_show_btn' type='button' class='btn btn-primary' value='Show Program' style='margin:15px;' onclick='obs_alerts_dtable.showColumn("site");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
                            <input id='site_hide_btn' type='button' class='btn btn-primary' value='Hide Program' style='display:none;margin:15px;' onclick='obs_alerts_dtable.hideColumn("site");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
                        @endif
                        <script type="text/javascript">
                            window.onload=filterText('Enrolled');
                        </script>
                    @else
                        <div style="text-align:center;margin:50px;">There are no patients under 20 minutes this month.
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@stop