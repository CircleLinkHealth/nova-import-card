@extends('partials.providerUI')

@section('title', 'Notes and Activities')
@section('activity', 'Notes/Offline Activities Review')

@section('content')

    @push('scripts')
        <script>
            const myUserId = @json(auth()->id());
        </script>
    @endpush

    @include('partials.confirm-modal')

    <div class="col-lg-8 col-lg-offset-2">
        <div>
            @include('core::partials.core::partials.errors.messages')
        </div>
        <div>
            @include('core::partials.errors.errors')
        </div>
    </div>


    <div class="row main-form-block" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-xs-12">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Notes and Activities
                </div>
                @include('partials.userheader')
                @if(! auth()->user()->isCareCoach() || (auth()->user()->isCareCoach() && app(\CircleLinkHealth\Customer\Policies\CreateNoteForPatient::class)->can(auth()->id(), $patient->id)))
                    <div class="col-sm-6 col-xs-4">
                        <a href="{{ route('patient.note.create', [$patient->id]) }}"
                           class="btn btn-primary btn-default form-item--button form-item-spacing" role="button">
                            + NEW NOTE
                        </a>
                    </div>
                @endif

                <div class="main-form-horizontal main-form-primary-horizontal col-md-12 col-xs-12"
                     style="border-top: 3px solid #50b2e2">
                    @if($activity_json)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div>
                        <br/>

                        @push('styles')
                            <style>
                                .webix_hcell {
                                    background-color: #d2e3ef;
                                }
                            </style>
                        @endpush

                        @push('scripts')
                            <script>

                                const activityStr = @json($activity_json) +"";
                                const activityJson = JSON.parse(activityStr);

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
                                    scrollX: true,
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
                                                if (obj.logged_from === "note" || obj.logged_from === "note_task")

                                                    if (obj.logger_id === myUserId && obj.status === "draft") {
                                                        return "<a href='<?php echo route('patient.note.edit', [
                                                            $patient->id,
                                                            '',
                                                        ]); ?>/" + obj.id + "'>" + obj.type_name + "</a>";
                                                    }
                                                    else {
                                                        return "<a href='<?php echo route('patient.note.view', [
                                                            $patient->id,
                                                            '',
                                                        ]); ?>/" + obj.id + "'>" + obj.type_name + "</a>";
                                                    }
                                                else if (obj.logged_from === "appointment") {
                                                    return "<a href='<?php echo route('patient.appointment.view', [
                                                        $patient->id,
                                                        '',
                                                    ]); ?>/" + obj.id + "'>" + obj.type_name + "</a>"
                                                } else {
                                                    return "<a href='<?php echo route('patient.activity.view', [
                                                        $patient->id,
                                                        '',
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
                                                if (obj.logged_from == "note") {
                                                    return "Note";
                                                } else if (obj.logged_from == "note_task") {
                                                    return "Note re: Task";
                                                } else if (obj.logged_from == "manual_input") {
                                                    return "Offline Activity";
                                                } else if (obj.logged_from == "appointment") {
                                                    return "Appointment";
                                                }
                                                return obj.type_name;
                                            },
                                            width: 120,
                                            sort: 'string'
                                        },
                                        {
                                            id: "tags",
                                            css: {'text-align': 'left', 'top': 0, 'left': 0, 'bottom': 0, 'right': 0},
                                            header: ["Status",],
                                            width: 110,
                                            sort: 'string'
                                        },
                                        {
                                            id: "comment",
                                            header: ["Preview"],
                                            template: function (obj) {
                                                if (obj.logged_from === "note" || obj.logged_from === "note_task") {
                                                    return "<a href='<?php echo route('patient.note.view', [
                                                        $patient->id,
                                                        '',
                                                    ]); ?>/" + obj.id + "' title='" + obj.comment + "'>" + obj.comment + "</a>";
                                                }
                                                else if (obj.logged_from === "manual_input" || obj.logged_from === "activity") {
                                                    return "<a href='<?php echo route('patient.activity.view', [
                                                        $patient->id,
                                                        '',
                                                    ]); ?>/" + obj.id + "'>" + obj.comment + "</a>"
                                                } else if (obj.logged_from === "appointment") {
                                                    return "<a href='<?php echo route('patient.appointment.view', [
                                                        $patient->id,
                                                        '',
                                                    ]); ?>/" + obj.id + "'>" + obj.comment + "</a>"
                                                } else
                                                    return obj.type_name;
                                            },
                                            fillspace: true,
                                            adjust: true,
                                            width: 176,
                                            sort: 'string',
                                            tooltip: false,
                                            moveToFront: true
                                        },
                                        {
                                            id: "performed_at",
                                            header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                            width: 100,
                                            sort: 'date'
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
                                        //CPM-725: Maximum Call Stack Size exceeded error on low-end machines
                                        this.config.autoheight = false;
                                    },
                                    pager: {
                                        animate: true,
                                        container: "paging_container",// the container where the pager controls will be placed into
                                        template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}@if(is_null($showAll)) <p></p>\n" +
                                            "@elseif($showAll == true)\n" +
                                            "<a\n" +
                                            "href=\"{{ route('patient.note.index', array('patientId' => $patient->id, 'showAll' => false)) }}\"\n" +
                                            "class=\"btn btn-primary btn-sm\"\n" +
                                            "role=\"button\">Show Last 2 Months</a>\n" +
                                            "@else\n" +
                                            "<a\n" +
                                            "href=\"{{ route('patient.note.index', array('patientId' => $patient->id, 'showAll' => true)) }}\"\n" +
                                            "class=\"btn btn-primary btn-sm\" role=\"button\">Show\n" +
                                            "All</a>\n" +
                                            "@endif",
                                        size: 10, // the number of records per a page
                                        group: 5   // the number of pages in the pager
                                    },
                                    data: activityJson
                                });
                                const debounced = _.debounce(() => {
                                    obs_alerts_dtable.adjust();
                                }, 1000);
                                webix.event(window, "resize", debounced);
                            </script>
                        @endpush

                        <div class="row">
                            @push('styles')
                                <style>
                                    li {
                                        padding-bottom: 2px;
                                    }
                                </style>
                            @endpush
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
                                    <div class="label label-warning" style="margin-right: 4px; text-align: right; background-color: #9865f2">
                                        <span class="glyphicon glyphicon-thumbs-up"></span>
                                    </div>
                                    Success Story
                                </li>

                                <li>
                                    <div class="label label-success" style="margin-right: 4px; text-align: right;">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </div>
                                    Forward Seen By Provider
                                </li>

                            </div>
                            @if(auth()->user()->hasRole(array_merge(['administrator'], \CircleLinkHealth\Customer\CpmConstants::PRACTICE_STAFF_ROLE_NAMES)) )

                                <input type="button" value="Export as PDF" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="webix.toPDF($$(obs_alerts_dtable), {
                                               header: 'CarePlan Manager notes for {{ $patient->getFullName() . ", Dr. " . $patient->getBillingProviderName() . " as of " . Carbon\Carbon::now()->toDateString() }}',
                                               orientation:'landscape',
                                               autowidth:true,
                                               filename: '{{$patient->getFullName() }} {{Carbon\Carbon::now()->toDateString()}}',
                                               columns:{
                                               'performed_at':       { header:'Date/Time', width: 200, template: webix.template('#performed_at#') },
                                               'logger_name':             { header:'Author Name',    width:200, sort:'string', template: webix.template('#logger_name#')},
                                               'comment':             { header:'Note Contents',    width:200, sort:'string', template: webix.template('#comment#')}

                                               }});">

                                <input type="button" value="Export as Excel" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="webix.toExcel($$(obs_alerts_dtable), {
                                               header:'CarePlan Manager notes for {{ $patient->getFullName() . ", Dr. " . $patient->getBillingProviderName() . " as of " . Carbon\Carbon::now()->toDateString() }}',
                                               orientation:'landscape',
                                               autowidth:true,
                                               filename: '{{$patient->getFullName() }} {{Carbon\Carbon::now()->toDateString()}}',

                                               columns:{
                                               'performed_at':       { header:'Date/Time', width: 110, template: webix.template('#performed_at#') },
                                               'logger_name':             { header:'Author Name',    width:75, sort:'string', template: webix.template('#logger_name#')},
                                               'comment':             { header:'Note Contents',    width:400, sort:'string', template: webix.template('#comment#')}

                                               }});">
                            @endif
                            @else
                                <div style="text-align:center;margin:50px;">There are no patient Notes/Offline
                                    Activities to
                                    display for this month.
                                </div>
                            @endif
                        </div>
                </div>
            </div>
        </div>
    </div>
@endsection
