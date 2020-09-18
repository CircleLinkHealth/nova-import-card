@extends('partials.providerUI')

@section('title', 'Patient CarePlan Print List')
@section('activity', 'Patient CarePlan Print List')

@section('content')

    <div class="container">
        <section class="main-form">
            <div class="row">
                <div class="">
                </div>
            </div>
            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <div class="main-form-title">Patient CarePlan Print List</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <div class="col-sm-2">
                            <h4 class="time-report__month"><?= date('F Y'); ?></h4>
                        </div>
                        <div class="col-sm-10">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row" style="border-bottom: #50b2e2 3px solid;">
                        @if($patientJson)
                            <div id="obs_alerts_container" class=""></div><br/>
                            <div id="paging_container"></div><br/>
                            <input id='lastName_btn' type='button' class='btn btn-primary' value='Show by Last Name'
                                   style='margin:15px;'
                                   onclick='obs_alerts_dtable.showColumn("last_name");obs_alerts_dtable.hideColumn("first_name");obs_alerts_dtable.sort("#last_name#");this.style.display = "none";getElementById("firstName_btn").style.display = "inline-block";'>
                            <input id='firstName_btn' type='button' class='btn btn-primary' value='Show by First Name'
                                   style='display:none;margin:15px;'
                                   onclick='obs_alerts_dtable.hideColumn("last_name");obs_alerts_dtable.showColumn("first_name");obs_alerts_dtable.sort("#first_name#");this.style.display = "none";getElementById("lastName_btn").style.display = "inline-block";'>
                            @if(auth()->user()->hasRole(array_merge(['administrator'], \CircleLinkHealth\Customer\CpmConstants::PRACTICE_STAFF_ROLE_NAMES)))
                                <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                                       onclick="webix.toPDF($$(obs_alerts_dtable), {
        header:'CarePlanManager.com - Patient CarePlan Print List',
        orientation:'landscape',
        autowidth:true,
                columns:{
        'first_name':       { header:'Patient Name', width: 200, template: webix.template('#first_name# #last_name#') },
        'provider':         { header:'Provider',    width:200, sort:'string', template: webix.template('#provider#') },
        'program_name':     { header:'Program',    width:150, sort:'string', template: webix.template('#program_name#')},
        'careplan_printed':       { header:'CarePlan Printed',    width:105, sort:'string', template: webix.template('#careplan_printed#')},
        // 'careplan_status':  { header:'CarePlan Status', tooltip:'#tooltip#' , width:125, template: webix.template('#careplan_status#')},
        'reg_date':         { header:'Registered On', width:120, sort:'string', template: webix.template('#reg_date#')}
                                                      }});">
                                <input type="button" value="Export as Excel" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="webix.toExcel($$(obs_alerts_dtable), {
                columns:{
        'first_name':       { header:'Patient Name', width: 200, template: webix.template('#first_name# #last_name#') },
        'provider':         { header:'Provider',    width:200, sort:'string', template: webix.template('#provider#') },
        'program_name':     { header:'Program',    width:150, sort:'string', template: webix.template('#program_name#')},
        'careplan_printed':       { header:'CarePlan Printed',    width:105, sort:'string', template: webix.template('#careplan_printed#')},
        // 'careplan_status':  { header:'CarePlan Status', tooltip:'#tooltip#' , width:125, template: webix.template('#careplan_status#')},
        'reg_date':         { header:'Registered On', width:120, sort:'string', template: webix.template('#reg_date#')},
                                                        }});">
                            @endif
                            <span id="print_list" class='print-list'></span>
                        @else
                            <div style="text-align:center;margin:50px;">There are no patients to display</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
    </form>
@stop

@push('scripts')
    <script>
        function filterText(text) {
            // var text = node;
            if (!text) return obs_alerts_dtable.filter();

            obs_alerts_dtable.filter(function (obj) {
                return obj.status_ccm == text;
            })
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

        function sortBySeconds(a, b) {
            a = a.ccm_seconds.parseInt;
            b = b.ccm_seconds.parseInt;
            return a > b ? 1 : (a < b ? -1 : 0);
        }

        function nameCompare(columnValue, filterValue, obj) {
            let value = obj.patient_name.toLowerCase();
            filterValue = filterValue.toLowerCase();
            return value.indexOf(filterValue) >= 0
        };

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
            select: "row",
            multiselect: true,
            blockselect: true,
            columns: [
                {
                    id: "first_name",
                    header: ["Patient Name", {content: "textFilter", compare: nameCompare, placeholder: "Filter"}],
                    template: "#first_name# #last_name#",
                    width: 100,
                    sort: 'string',
                    adjust: true,
                    fillspace: true
                },
                {
                    id: "last_name",
                    header: ["Patient Name", {content: "textFilter", compare: nameCompare, placeholder: "Filter"}],
                    template: "#last_name#, #first_name#",
                    width: 120,
                    sort: 'string',
                    adjust: true,
                    fillspace: true
                },
                {
                    id: "careplan_printed",
                    header: ["Printed", {content: "selectFilter"}],
                    width: 120,
                    sort: 'text',
                    template: function (obj) {
                        if (obj.careplan_printed == 'Yes') {
                            return "<span style='float:left;' title='Last Printed: " + obj.careplan_last_printed + "'>Yes</span>";
                        } else {
                            return "<span style='float:left; title='No'>Select to Print</span>";
                        }
                    }
                },
                {
                    id: "careplan_status",
                    header: ["CP Status", {content: "selectFilter", placeholder: "Filter"}],
                    width: 200,
                    template: function (obj) {
                        return "" + obj.careplan_status_link + "";
                    }
                },
                {
                    id: "program_name",
                    header: ["Program", {content: "selectFilter", placeholder: "Filter"}],
                    width: 175,
                    sort: 'string'
                },
                {
                    id: "provider",
                    header: ["Provider", {content: "selectFilter", placeholder: "Filter"}],
                    width: 150,
                    sort: 'string'
                },
                {
                    id: "reg_date",
                    header: ["Registered On", {content: "textFilter", placeholder: "Filter"}],
                    width: 120,
                    sort: 'string',
                    template: function (obj) {
                        return "<span style='float:right;'>" + obj.reg_date + "</span>";
                    }
                },

            ],
            ready: function () {
                //CPM-725: Maximum Call Stack Size exceeded error on low-end machines
                this.config.autoheight = false;
            },
            pager: {
                animate: true,
                container: "paging_container",// the container where the pager controls will be placed into
                template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                size: 10, // the number of records per a page
                group: 5   // the number of pages in the pager
            },
            on: {
                onSelectChange: function () {
                    var text = obs_alerts_dtable.getSelectedId(true).join();
                    var textmsg = "<a href='{!! route('patients.careplan.multi')!!}?users=" + text + "&letter' class='btn btn-primary'>Print Selected</a>";
                    document.getElementById('print_list').innerHTML = textmsg + '\n<BR>';
                }
            },
            data: {!! $patientJson !!}
        });

        const debounced = _.debounce(() => {
            obs_alerts_dtable.adjust();
        }, 1000);
        webix.event(window, "resize", debounced);

        obs_alerts_dtable.sort("#patient_name#");
        obs_alerts_dtable.filter("#careplan_printed#", "No");
        obs_alerts_dtable.hideColumn("last_name");

        // window.onload=filterText('#careplan_last_printed#','X');
        // obs_alerts_dtable.hideColumn("status_ccm");

        // window.onload=filterText('');
        // obs_alerts_dtable.hideColumn("ccm_status");
    </script>
@endpush
