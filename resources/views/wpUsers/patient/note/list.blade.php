@extends('partials.providerUI')

@section('title', 'All Patient Notes')

@section('content')

    <?php
    if(isset($results)){
                $webix = "data:" . json_encode(array_values($results)) . "";
    }

    ?>
    <div class="row main-form-block" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row ">
                <div class="main-form-title col-lg-12">
                    All Patient Notes
                </div>

                <div class="col-sm-3">
                    <h4 class="time-report__month"></h4>
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.listing'), 'method' => 'GET', 'class' => 'form-horizontal', 'style' => 'margin-right: 10px')) !!}
                <div class="form-group  pull-right" style="margin-top:10px; ">
                        <span class="glyphicon glyphicon-user" aria-hidden="true" style="color: #63bbe8; font-size: 28px; top: 0.4em;"></span>

                        <label for="provider" class="sr-only">Select Month:</label>

                        <select name="provider" id="provider" class="selectpicker" data-width="200px" required="required"
                                data-size="10" style="display: none;">
                            <option value="">Select Provider</option>
                            @foreach($providers_for_blog as $key => $value)
                                @if(isset($selected_provider) && $selected_provider->ID == $key)
                                    <option value="{{$selected_provider->ID}}" selected>{{$selected_provider->display_name}}</option>
                                @else
                                <option value={{$key}}>{{$value}}</option>
                                @endif
                            @endforeach
                        </select>

                    <div class="inline-block">
                        <i class="icon icon--date-time" style="margin-left:16px"></i>
                        <label for="month" class="sr-only">Select Month:</label>
                        <select name="month" id="month" class="selectpicker" data-width="200px" required="required"
                                data-size="10" style="display: none;">
                            <option value="">Select Month</option>
                            @for($i = 0; $i < count($months); $i++)
                                <option value="{{$i+1}}" @if($month_selected == $i+1) {{'selected'}} @endif>{{$months[$i]}}</option>
                            @endfor
                        </select>

                        <div class="inline-block">
                            <label for="year" class="sr-only">Select Year:</label>
                            <select name="year" id="year" class="selectpicker" data-width="150px" style="display: none;" required="required">
                                <option value="">Select Year</option>
                            @foreach($years as $year)
                                    <option value="{{$year}}"
                                            @if($year_selected == $year){{'selected'}}@endif>{{$year}}</option>
                                @endforeach
                            </select>
                        <button type="submit" id="find" class="btn btn-primary">Go</button>
                        {!! Form::close() !!}
                        </div>
                    </div>
                </div>


                <div class="main-form-horizontal main-form-primary-horizontal col-md-12" style="border-top: 3px solid #50b2e2">
                   @if($isProviderSelected)
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
                                        template:"<a href='<?php echo URL::route('patient.note.view', array('patient' => '#patient_id#','noteId'=>'#id#')); ?>'>#patient_name#</a>"


                                    },
                                    {
                                        id: "program_name",
                                        header: ["Program", {content: "selectFilter", placeholder: "Filter"}],
                                        width: 150,
                                        sort: 'string',
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
                                        width: 225,
                                        sort: 'string',
                                        tooltip:['#comment#'],
                                        fillspace: true
                                    }
                                ],

                                ready: function () {
                                    this.adjustRowHeight("obs_key");
                                },

                                pager: {
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 15, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },


                                <?php echo $webix  ?>
                            });


                            obs_alerts_dtable.hideColumn("program_name");

                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            });

                        </script>
                        <div class="row">
                            <style>
                                li{padding-bottom: 2px;}
                            </style>
                            <div class="col-sm-6" style="padding: 10px; top: -14px">
                                <li><div class="label label-info" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                                    </div>
                                    Patient Reached</li>

                                <li><div class="label label-danger" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-flag"></span>
                                    </div>
                                    Patient in ER</li>

                                <li><div class="label label-warning" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-envelope"></span>
                                    </div>
                                    Forwarded To Provider</li>
                            </div>

                            <div class="col-sm-6">
                                <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:1px;'
                                       onclick="webix.toPDF(obs_alerts_dtable);">
                                <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:1px;'
                                       onclick="webix.toExcel(obs_alerts_dtable);">
                                @if ( !Auth::guest() && Auth::user()->can(['admin-access']))
                                    <input id='site_show_btn' type='button' class='btn btn-primary' value='Show Program' style='margin:4px;' onclick='obs_alerts_dtable.showColumn("program_name");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
                                    <input id='site_hide_btn' type='button' class='btn btn-primary' value='Hide Program' style='display:none;margin:4px;' onclick='obs_alerts_dtable.hideColumn("program_name");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
                                @endif
                            </div>
                        </div>
                </div>
                @else
                    <div style="text-align:center;margin:50px;">There are no patients notes for {{$selected_provider->display_name}}.
                    </div>
                @endif
              @else
                    <div style="text-align:center;margin:50px;">Please select a Provider.
                    </div>
                @endif
                <div id="rohstar" style="color: #00ACC1">

{{--                    {!! $results->render()  !!}--}}
                </div>
        </div>
    </div>
    @stop