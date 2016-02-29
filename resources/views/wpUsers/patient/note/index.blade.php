@extends('partials.providerUI')

@section('title', 'Patient Notes')
@section('activity', 'Notes/Offline Activities Review')

@section('content')

    <div class="row main-form-block" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Notes / Offline Activities
                </div>
                @include('partials.userheader')
                <div class="col-sm-2" >
                    <a href="{{ URL::route('patient.note.create', array('patient' => $patient->ID)) }}"
                       class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">+NEW
                        NOTE</a><br>
                </div>
                <div class="main-form-horizontal main-form-primary-horizontal col-md-12" style="border-top: 3px solid #50b2e2">
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
                                                    return "<a href='<?php echo URL::route('patient.note.view', array('patientId' => $patient->ID)); ?>/"  + obj.id + "'>" + obj.type + "</a>"
                                                else if (obj.logged_from == "manual_input" || obj.logged_from == "activity") {
                                                    return  "<a href='<?php echo URL::route('patient.activity.view', array('patientId' => $patient->ID)); ?>/"  + obj.id + "'>" + obj.type + "</a>"
                                                }
                                                return obj.type_name;
                                            },

                                            width: 175,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "left"}
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
                                                return obj.type_name;
                                            },
                                            width: 120,
                                            sort: 'string'
                                        },
                                        {
                                            id: "comment",
                                            header: ["Preview"],
                                            fillspace: true,
                                            width: 400,
                                            sort: 'string'
                                        },
                                        {
                                            id: "performed_at",
                                            header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                            width: 100
                                        },

                                        {
                                            id: "logger_name",
                                            header: ["Provider", {content: "textFilter", placeholder: "Filter"}],
                                            width: 210,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "left"}
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