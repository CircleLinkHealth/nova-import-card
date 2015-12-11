@extends('partials.providerUI')
@section('content')
<div class="main-form-title">
    Notes / Offline Activities
</div>
@include('partials.userheader')
                <div class="col-sm-2">
                    <a href="#" class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">+NEW NOTE</a><br>
                </div>
                    <form action="" method="POST">
                        <div class="form-group  pull-right" style="margin-top:10px;">
                            <i class="icon icon--date-time"></i>
                            <div class="inline-block">
                                <label for="selectMonth" class="sr-only">Select Month:</label>
                                <select name="selectMonth" id="selectMonth" class="selectpicker" data-width="200px" data-size="10" style="display: none;">
                                    <option value="">Select Month</option>
                                    <option value="01">Jan</option>
                                    <option value="02">Feb</option>
                                    <option value="03">Mar</option>
                                    <option value="04">Apr</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">Aug</option>
                                    <option value="09">Sept</option>
                                    <option value="10">Oct</option>
                                    <option value="11">Nov</option>
                                    <option value="12" selected="selected">Dec</option>
                            </select>
                            <div class="inline-block">
                                <label for="selectYear" class="sr-only">Select Year:</label>
                                <select name="selectYear" id="selectYear" class="selectpicker" data-width="100px" data-size="10" style="display: none;">
                                    @foreach($years as $year)
                                    <option value="{{$year}}" selected="selected">{{$year}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" value="Search" name="find" id="find" class="btn btn-primary">Go</button>
                        </div>
                        </div>
                    </form>
                    <div class="row">
                            <div class="row">
                                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                                @if($activity_json)
                                <div id="obs_alerts_container" class=""></div><br/>
                                <div id="paging_container"></div><br/>
                                <style>
                                    .webix_hcell{
                                        background-color:#d2e3ef;
                                    }
                                </style>                        <script>
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

                                    obs_alerts_dtable = new webix.ui({
                                        container:"obs_alerts_container",
                                        view:"datatable",
                                        //css:"webix_clh_cf_style",
                                        autoheight:true,
                                        fixedRowHeight:false,  rowLineHeight:25, rowHeight:25,
                                        // leftSplit:2,
                                        scrollX:false,
                                        resizeColumn:true,
                                        footer:true,
                                        columns:[
                                            {id:"type_name",    header:["Topic / Offline Activity",{content:"textFilter", placeholder:"Filter"}],
                                                template:function(obj){
                                                    if (obj.logged_from == "note")
                                                        return "#";
                                                    else if(obj.logged_from == "manual_input"){
                                                        return "#";
                                                    }
                                                    return obj.type_name;
                                                },

                                                width:175, sort:'string' , css:{ "color":"black","text-align":"right" }},
                                            {id:"type",   header:["Type",{content:"textFilter", placeholder:"Filter"}],    width:120, sort:'string'},
                                            {id:"comment",   header:["Preview"],    fillspace:true ,width:200, sort:'string'},
                                            {id:"performed_at",   header:["Date",{content:"textFilter", placeholder:"Filter"}],    width:100, sort:'string'},

                                            {id:"logger_id",    header:["Provider",{content:"textFilter", placeholder:"Filter"}],    width:210, sort:'string' , css:{ "color":"black","text-align":"right" }},
                                        ],
                                        ready:function(){
                                            this.adjustRowHeight("obs_key");
                                        },
                                        /*ready:function(){
                                         this.adjustRowHeight("obs_value");
                                         },*/
                                        pager:{
                                            container:"paging_container",// the container where the pager controls will be placed into
                                            template:"{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                            size:10, // the number of records per a page
                                            group:5   // the number of pages in the pager
                                        },
                                    {!!$activity_json!!}
                                    });
                                    webix.event(window, "resize", function(){ obs_alerts_dtable.adjust(); })
                                </script>
                                <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;' onclick="obs_alerts_dtable.exportToPDF();">
                                <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;' onclick="obs_alerts_dtable.exportToExcel();">
                                @else
                                <div style="text-align:center;margin:50px;">There are no patient Notes/Offline Activities to display for this month.</div>
                                @endif
                            </div>
                        </div>

                    </div>
                    </section>
                </div>
                </div>
            </div>


@stop