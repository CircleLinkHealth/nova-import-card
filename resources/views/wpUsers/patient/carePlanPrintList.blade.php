@extends('partials.providerUI')

@section('title', 'Care Plan Print List')
@section('activity', 'Care Plan Print List')

@section('content')
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Care Plan Print List
                </div>
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">


                    <?php if (strlen($patientJson) > 20) {
    ?>
                    <div id="obs_alerts_container" class=""></div>
                    <br/>
                    <div id="paging_container"></div>
                    <br/>

                    <input id='lastName_btn' type='button' class='btn btn-primary' value='Show by Last Name'
                           style='margin:15px;'
                           onclick='obs_alerts_dtable.showColumn("last_name");obs_alerts_dtable.hideColumn("first_name");obs_alerts_dtable.sort("#last_name#");this.style.display = "none";getElementById("firstName_btn").style.display = "inline-block";'>
                    <input id='firstName_btn' type='button' class='btn btn-primary' value='Show by First Name'
                           style='display:none;margin:15px;'
                           onclick='obs_alerts_dtable.hideColumn("last_name");obs_alerts_dtable.showColumn("first_name");obs_alerts_dtable.sort("#first_name#");this.style.display = "none";getElementById("lastName_btn").style.display = "inline-block";'>
                    <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                           onclick="webix.toPDF(obs_alerts_dtable);">
                    <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                           onclick="webix.toExcel(obs_alerts_dtable);">

                    <?php
} else {
        ?>
                    <div style="text-align:center;margin:50px;">There are no patients to display</div>
                    <?php
    } ?>


                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        function filterText(text) {
            // var text = node;
            if (!text) return obs_alerts_dtable.filter();

            obs_alerts_dtable.filter(function (obj) {
                return obj.ccm_status == text;
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
            //tooltip:true,
            columns: [
                {
                    id: "first_name",
                    header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                    template: "<a href='<?php echo route(
            'patient.summary',
            ['patientId' => '#key#']
        ); ?>'>#first_name# #last_name#</a>",
                    width: 100,
                    sort: 'string',
                    adjust: true,
                    fillspace: true
                },
                {
                    id: "last_name",
                    header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                    template: "<a href='<?php echo route(
            'patient.summary',
            ['patientId' => '#key#']
        ); ?>'>#last_name#, #first_name#</a>",
                    width: 120,
                    sort: 'string',
                    adjust: true,
                    fillspace: true
                },
                {id: "provider", header: ["Provider", {content: "selectFilter"}], width: 105, sort: 'string'},
                {id: "ccm_status", header: ["CCM Status", {content: "selectFilter"}], width: 105, sort: 'string'},
                {
                    id: "careplan_status",
                    header: ["CarePlan Status", {content: "selectFilter", placeholder: "Filter"}],
                    tooltip: "#tooltip#",
                    width: 125,
                    template: function (obj) {
                        return "" + obj.careplan_status_link + "";
                    }
                },
                {
                    id: "dob",
                    header: ["DOB", {content: "dateFilter", placeholder: "Filter"}],
                    width: 100,
                    sort: 'string'
                },
                {id: "phone", header: ["Phone", {content: "textFilter", placeholder: "Filter"}], width: 120},
                {
                    id: "age", header: ["Age", {content: "numberFilter", placeholder: "Filter"}], width: 50,
                    template: function (obj) {
                        return "<span style='float:right;'>" + obj.age + "</span>";
                    }
                },
                {
                    id: "reg_date",
                    header: ["Registered On", {content: "dateFilter", placeholder: "Filter"}],
                    width: 120,
                    sort: 'string',
                    template: function (obj) {
                        return "<span style='float:right;'>" + obj.reg_date + "</span>";
                    }
                },
                {
                    id: "last_read",
                    header: ["Last Reading", {content: "textFilter", placeholder: "Filter"}],
                    width: 120,
                    sort: 'string',
                    template: function (obj) {
                        return "<span style='float:right;'>" + obj.last_read + "</span>";
                    }
                },
                {
                    id: "ccm_seconds",
                    header: ["CCM", "(Min:Sec)"],
                    width: 80,
                    sort: 'int',
                    css: {"color": "black", "text-align": "right"},
                    format: webix.numberFormat,
                    template: function (obj, common) {
                        var seconds = obj.ccm_seconds;
                        var date = new Date(seconds * 1000);
                        var mm = Math.floor(seconds / 60);
                        var ss = date.getSeconds();
                        return "<span style='float:right;'>" + mm + ":" + zeroPad(ss, 10) + "</span>";
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
            data: <?php echo $patientJson; ?>
        });
        const debounced = _.debounce(() => {
            obs_alerts_dtable.adjust();
        }, 1000);
        webix.event(window, "resize", debounced);
        obs_alerts_dtable.sort("#patient_name#");
        obs_alerts_dtable.hideColumn("last_name");
    </script>
    <script type="text/javascript">
        function onLoad() {
            if (typeof filterText === 'undefined') {
                setTimeout(() => onLoad(), 200);
                return;
            }
            filterText('');
        }

        window.onload = onLoad;
        // obs_alerts_dtable.hideColumn("ccm_status");
    </script>
@endpush
