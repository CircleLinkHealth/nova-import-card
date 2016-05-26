@extends('partials.providerUI')
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
                            function startCompare(value, filter){
                                value = value.toString().toLowerCase();
                                filter = '<'+filter.toString().toLowerCase();
                                return value.indexOf(filter) === 0;
                            }
                            webix.locale.pager = {
                                first: "<<",// the first button
                                last: ">>",// the last button
                                next: ">",// the next button
                                prev: "<"// the previous button
                            };
                            webix.ui.datafilter.mySummColumn = webix.extend({
                                refresh:function(master, node, value){
                                    var seconds = 0;
                                    master.data.each(function(obj){
                                        seconds = seconds+parseInt(obj.duration);
                                    });
                                    var date = new Date(seconds * 1000);
                                    var mm = Math.floor(seconds/60);
                                    var ss = date.getSeconds();
                                    if (ss < 10) {ss = "0"+ss;}
                                    var time = ""+mm+":"+ss;
                                    result = "<span title='"+mm+":"+ss+"' style='float:right;'><b>" + time + "</b></span>";
                                    node.firstChild.innerHTML = result;
                                }
                            }, webix.ui.datafilter.summColumn);

                            obs_alerts_dtable = new webix.ui({
                                container:"obs_alerts_container",
                                view:"datatable",
                                autoheight:true,
                                fixedRowHeight:false,  rowLineHeight:25, rowHeight:25,
                                scrollX:true,
                                resizeColumn:true,
                                footer:false,
                                columns:[

                                    {id:"patient_name",   header:["Patient Name",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"program_name",   header:["Program",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"provider_name",   header:["Program",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"author_name", header:["Author",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"tags", css:{'text-align':'left','top': 0, 'left': 0, 'bottom': 0, 'right': 0},  header:["Status",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"type",  header:["Type",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"comment",  header:["Preview",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},
                                    {id:"date",  header:["Date",{content:"textFilter", placeholder:"Filter"}],    width:150, sort:'string'},


                                ],
                                ready:function(){
                                    this.adjustRowHeight("obs_key");
                                },
                                /*ready:function(){
                                 this.adjustRowHeight("obs_value");
                                 },*/
                                pager:{
                                    animate:true,
                                    container:"paging_container",// the container where the pager controls will be placed into
                                    template:"{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size:10, // the number of records per a page
                                    group:5   // the number of pages in the pager
                                },
                                {!! $notes !!}                         });
                            webix.event(window, "resize", function(){ obs_alerts_dtable.adjust(); })
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