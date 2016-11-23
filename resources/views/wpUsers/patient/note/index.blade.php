@extends('partials.providerUI')

@section('title', 'Patient Notes')
@section('activity', 'Notes/Offline Activities Review')

@section('content')

    <div class="row main-form-block" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Notes / Offline Activities
                </div>
                @include('partials.userheader')
                <div class="col-sm-2">
                    <a href="{{ URL::route('patient.note.create', array('patient' => $patient->id)) }}"
                       class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">+NEW
                        NOTE</a><br>
                </div>
                <div class="main-form-horizontal main-form-primary-horizontal col-md-12"
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

                            obs_alerts_dtable = new webix.ui({
                                container: "obs_alerts_container",
                                view: "datatable",
                                //css:"webix_clh_cf_style",
                                autoheight: true,
                                fixedRowHeight: true, rowLineHeight: 25, rowHeight: 25,
                                // leftSplit:2,
                                scrollX: false,
                                resizeColumn: true,
                                footer: true,
                                tooltip: true,
                                columns: [
                                    {
                                        id: "type_name",
                                        header: ["Topic / Offline Activity", {
                                            content: "textFilter",
                                            placeholder: "Filter"
                                        }],
                                        template: function (obj) {
                                            if (obj.logged_from == "note")
                                                return "<a href='<?php echo route('patient.note.view', [
                                                                'patientId' => $patient->id,
                                                                'noteId'    => ''
                                                        ]); ?>/" + obj.id + "'>" + obj.type_name + "</a>";
                                            else if (obj.logged_from == "appointment") {
                                                return "Appointment";
                                            } else {
                                                return "<a href='<?php echo route('patient.activity.view', [
                                                                'patientId' => $patient->id,
                                                                'actId'     => ''
                                                        ]); ?>/" + obj.id + "'>" + obj.type_name + "</a>"
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
                                        id: "tags",
                                        css: {'text-align': 'left', 'top': 0, 'left': 0, 'bottom': 0, 'right': 0},
                                        header: ["Status"],
                                        width: 110,
                                        sort: 'string'
                                    },
                                    {
                                        id: "comment",
                                        header: ["Preview"],
                                        template: function (obj) {
                                            if (obj.logged_from == "note")
                                                return "<a href='<?php echo route('patient.note.view', [
                                                                'patientId' => $patient->id,
                                                                'noteId'    => ''
                                                        ]); ?>/" + obj.id + "'>" + obj.comment + "</a>";
                                            else if (obj.logged_from == "manual_input" || obj.logged_from == "activity") {
                                                return "<a href='<?php echo route('patient.activity.view', [
                                                                'patientId' => $patient->id,
                                                                'actId'     => ''
                                                        ]); ?>/" + obj.id + "'>" + obj.comment + "</a>"
                                            }else if (obj.logged_from == "appointment"){
                                                return "<a href='<a href='<?php echo route('patient.appointment.view', [
                                                                'patientId' => $patient->id,
                                                                'appointmentId'     => ''
                                                        ]); ?>/" + obj.id + "'>" + obj.comment + "</a>"
                                            } else
                                            return obj.type_name;
                                        },
                                        fillspace: true,
                                        width: 400,
                                        sort: 'string',
                                        tooltip: ['#comment#']
                                    },
                                    {
                                        id: "performed_at",
                                        header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                        width: 100,
                                        sort: 'string'
                                    },

                                    {
                                        id: "logger_name",
                                        header: ["Author", {content: "textFilter", placeholder: "Filter"}],
                                        width: 210,
                                        sort: 'string',
                                        css: {"color": "black", "text-align": "left"}
                                    },
                                ],
                                ready: function () {
                                    this.adjustRowHeight("comment");
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
                                {!!$activity_json!!}
                            })
                            ;
                            webix.event(window, "resize", function () {
                                obs_alerts_dtable.adjust();
                            })
                        </script>

                        <div class="row">
                            <style>
                                li {
                                    padding-bottom: 2px;
                                }
                            </style>
                            <div class="col-sm-6" style="padding: 10px; top: -14px">
                                <li>
                                    <div class="label label-info" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
                                    </div>
                                    Patient Reached
                                </li>

                                <li>
                                    <div class="label label-danger" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-flag"></span>
                                    </div>
                                    Patient recently in ER or Hospital
                                </li>

                                <li>
                                    <div class="label label-warning" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-envelope"></span>
                                    </div>
                                    Forwarded To Provider
                                </li>

                                <li>
                                    <div class="label label-success" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </div>
                                    Forward Seen By Provider
                                </li>

                            </div>
                        @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))

                                <input type="button" value="Export as PDF" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="webix.toPDF($$(obs_alerts_dtable), {
                                               header: 'Circlelink Health notes for {!!  $patient->fullName . ", Dr. " . $patient->billingProviderName . " as of " . Carbon\Carbon::now()->toDateString() !!}',
                                               orientation:'landscape',
                                               autowidth:true,
                                               filename: 'PatientNotesReport{{Carbon\Carbon::now()->toDateString()}}',
                                               columns:{
                                               'performed_at':       { header:'Date/Time', width: 200, template: webix.template('#performed_at#') },
                                               'logger_name':             { header:'Author Name',    width:200, sort:'string', template: webix.template('#logger_name#')},
                                               'comment':             { header:'Note Contents',    width:200, sort:'string', template: webix.template('#comment#')}

                                               }});">

                                <input type="button" value="Export as Excel" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="webix.toExcel($$(obs_alerts_dtable), {
                                               header:'Circlelink Health notes for {!! $patient->fullName . ", Dr. " . $patient->billingProviderName . " as of " . Carbon\Carbon::now()->toDateString() !!}',
                                               orientation:'landscape',
                                               autowidth:true,
                                               filename: 'PatientNotesReport{{Carbon\Carbon::now()->toDateString()}}',

                                                columns:{
                                               'performed_at':       { header:'Date/Time', width: 200, template: webix.template('#performed_at#') },
                                               'logger_name':             { header:'Author Name',    width:200, sort:'string', template: webix.template('#logger_name#')},
                                               'comment':             { header:'Note Contents',    width:200, sort:'string', template: webix.template('#comment#')}

                                               }});">
                        @endif
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