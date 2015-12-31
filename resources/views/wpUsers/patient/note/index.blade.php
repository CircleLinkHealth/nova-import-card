@extends('partials.providerUI')
@section('content')
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title">
                    Notes / Offline Activities
                </div>
                @include('partials.userheader')
                <div class="col-sm-2">
                    <a href="{{ URL::route('patient.note.create', array('patient' => $patient->ID)) }}"
                       class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">+NEW
                        NOTE</a><br>
                </div>
                {!! Form::open(array('url' => URL::route('patient.note.index', ['patientId' => $patient]), 'method' => 'GET', 'class' => 'form-horizontal')) !!}
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
                                    <option value="{{$year}}" selected="selected">{{$year}}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" value="Search" name="find" id="find" class="btn btn-primary">Go</button>
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
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
                                        id: "type_name",
                                        header: ["Topic / Offline Activity", {
                                            content: "textFilter",
                                            placeholder: "Filter"
                                        }],
                                        template: function (obj) {
                                            if (obj.logged_from == "note")
                                            return "<a href='http://clapi.cpm.com/manage-patients/{{$patient->ID}}/notes/view/"+obj.id+"'>" + obj.type + "</a>"
                                            else if (obj.logged_from == "manual_input") {
                                                return obj.type;
                                            }
                                            return obj.type_name;
                                        },

                                        width: 175,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "right"}
                                    },
                                    {
                                        id: "logged_from",
                                        header: ["Type", {content: "textFilter", placeholder: "Filter"}],
                                        template: function (obj) {
                                            if (obj.logged_from == "note")
                                                return "Note";
                                            else if (obj.logged_from == "manual_input") {
                                                return "Offline Activity";
                                            }
                                            return obj.type_namez;
                                        },
                                        width: 120,
                                        sort: 'string'
                                    },
                                    {
                                        id: "comment",
                                        header: ["Preview"],
                                        fillspace: true,
                                        width: 200,
                                        sort: 'string'
                                    },
                                    {
                                        id: "performed_at",
                                        header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                        width: 100,
                                        sort: 'string'
                                    },

                                    {
                                        id: "logger_name",
                                        header: ["Provider", {content: "textFilter", placeholder: "Filter"}],
                                        width: 210,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "right"}
                                    },
                                ],
                                ready: function () {
                                    this.adjustRowHeight("obs_key");
                                },
                                /*ready:function(){
                                 this.adjustRowHeight("obs_value");
                                 },*/
                                pager: {
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 10, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },
                            {!!$activity_json!!}
                            })
                            ;
                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            })
                        </script>
                        <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                               onclick="obs_alerts_dtable.exportToPDF();">
                        <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                               onclick="obs_alerts_dtable.exportToExcel();">
                    @else
                        <div style="text-align:center;margin:50px;">There are no patient Notes/Offline Activities to
                            display for this month.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@stop