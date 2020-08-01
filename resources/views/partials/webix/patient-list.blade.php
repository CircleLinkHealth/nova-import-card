@if(strlen($patientJson) > 20)
    <div id="obs_alerts_container" class=""></div>
    <br/>
    <div id="paging_container"></div>
    <br/>

    @push('scripts')
        <script>
            function filterText(text) {
                // var text = node;
                if (typeof(obs_alerts_dtable) !== 'undefined') {
                    if (!text) return obs_alerts_dtable.filter();
                    else obs_alerts_dtable.filter(function (obj) {
                        return obj.ccm_status == text;
                    })
                }
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
            }

            obs_alerts_dtable = new webix.ui({
                container: "obs_alerts_container",
                view: "datatable",
                //css:"webix_clh_cf_style",
                autoheight: true,
                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                // leftSplit:2,
                scrollX: true,
                resizeColumn: true,
                //tooltip:true,
                columns: [
                    {
                        id: "first_name",
                        header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                        template: "<a href='<?php echo route(
    'patient.careplan.print',
    ['#key#']
); ?>'>#first_name# #last_name#</a>",
                        width: 200,
                        sort: 'string'
                    },
                    {
                        id: "last_name",
                        header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                        template: "<a href='<?php echo route(
    'patient.careplan.print',
    ['#key#']
); ?>'>#last_name#, #first_name#</a>",
                        width: 200,
                        sort: 'string'
                    },
                    {
                        id: "provider",
                        header: ["Provider", {content: "selectFilter"}],
                        width: 200,
                        sort: 'string'
                    },
                    {
                        id: "location",
                        header: ["Location", {content: "selectFilter"}],
                        width: 200,
                        sort: 'string'
                    },
                    {
                        id: "site",
                        header: ["Program", {content: "selectFilter"}],
                        width: 150,
                        sort: 'string'
                    },
                    {
                        id: "ccm_status",
                        header: ["CCM Status", {content: "selectFilter"}],
                        width: 105,
                        sort: 'string'
                    },
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
                    {
                        id: "phone",
                        header: ["Phone", {content: "textFilter", placeholder: "Filter"}],
                        width: 120
                    },
                    {
                        id: "age",
                        header: ["Age", {content: "numberFilter", placeholder: "Filter"}],
                        width: 50,
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
            obs_alerts_dtable.hideColumn("site");
        </script>
    @endpush

    <input id='lastName_btn' type='button' class='btn btn-primary' value='Show by Last Name'
           style='margin:15px;'
           onclick='obs_alerts_dtable.showColumn("last_name");obs_alerts_dtable.hideColumn("first_name");obs_alerts_dtable.sort("#last_name#");this.style.display = "none";getElementById("firstName_btn").style.display = "inline-block";'>
    <input id='firstName_btn' type='button' class='btn btn-primary' value='Show by First Name'
           style='display:none;margin:15px;'
           onclick='obs_alerts_dtable.hideColumn("last_name");obs_alerts_dtable.showColumn("first_name");obs_alerts_dtable.sort("#first_name#");this.style.display = "none";getElementById("lastName_btn").style.display = "inline-block";'>
    @if ($isAdmin || $isProvider || $isPracticeStaff)
        <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
               onclick="webix.toPDF($$(obs_alerts_dtable), {
                                header:'CarePlanManager.com - Patient List',
                                orientation:'landscape',
                                autowidth:true,
                                        columns:{
                                'first_name':       { header:'Patient Name', width: 200, template: webix.template('#first_name# #last_name#') },
                                'provider':         { header:'Provider',    width:200, sort:'string', template: webix.template('#provider#') },
                                'location':         { header:'Location',    width:200, sort:'string', template: webix.template('#location#') },
                                'site':             { header:'Program',    width:150, sort:'string', template: webix.template('#site#')},
                                'ccm_status':       { header:'CCM Status',    width:105, sort:'string', template: webix.template('#ccm_status#')},
                                'careplan_status':  { header:'CarePlan Status', tooltip:'#tooltip#' , width:125, template: webix.template('#careplan_status#')},
                                'dob':              { header:'DOB',    width:100, sort:'string', template: webix.template('#dob#')},
                                'phone':            { header:'Phone',    width:120, template: webix.template('#phone#')},
                                'age':              { header:'Age', width:50, template: webix.template('#age#')},
                                'reg_date':         { header:'Registered On', width:120, sort:'string', template: webix.template('#reg_date#')},
                                'last_read':        { header:'Last Reading', width:120, sort:'string', template: webix.template('#last_read#')},
                                'ccm_seconds':      { header:'CCM', width:80, template:function(obj, common) {
                                        var seconds = obj.ccm_seconds;
                                        var date = new Date(seconds * 1000);
                                        var mm = Math.floor(seconds/60);
                                        var ss = date.getSeconds();
                                        return mm + ':' + zeroPad(ss,10);}}}
                                                                              });">

        <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
               onclick="webix.toExcel($$(obs_alerts_dtable), {
                                        columns:{
                                'first_name':       { header:'Patient Name', width: 200, template: webix.template('#first_name# #last_name#') },
                                'provider':         { header:'Provider',    width:200, sort:'string', template: webix.template('#provider#') },
                                'location':         { header:'Location',    width:200, sort:'string', template: webix.template('#location#') },
                                'site':             { header:'Program',    width:150, sort:'string', template: webix.template('#site#')},
                                'ccm_status':       { header:'CCM Status',    width:105, sort:'string', template: webix.template('#ccm_status#')},
                                'careplan_status':  { header:'CarePlan Status', tooltip:'#tooltip#' , width:125, template: webix.template('#careplan_status#')},
                                'dob':              { header:'DOB',    width:100, sort:'string', template: webix.template('#dob#')},
                                'phone':            { header:'Phone',    width:120, template: webix.template('#phone#')},
                                'age':              { header:'Age', width:50, template: webix.template('#age#')},
                                'reg_date':         { header:'Registered On', width:120, sort:'string', template: webix.template('#reg_date#')},
                                'last_read':        { header:'Last Reading', width:120, sort:'string', template: webix.template('#last_read#')},
                                'ccm_seconds':      { header:'CCM', width:80, template:function(obj, common) {
                                        var seconds = obj.ccm_seconds;
                                        var date = new Date(seconds * 1000);
                                        var mm = Math.floor(seconds/60);
                                        var ss = date.getSeconds();
                                        return mm + ':' + zeroPad(ss,10);}}}});">
    @endif
    @if ( !Auth::guest() && Auth::user()->hasRole(['administrator', 'saas-admin']))
        <input id='site_show_btn' type='button' class='btn btn-primary' value='Show Program'
               style='margin:15px;'
               onclick='obs_alerts_dtable.showColumn("site");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
        <input id='site_hide_btn' type='button' class='btn btn-primary' value='Hide Program'
               style='display:none;margin:15px;'
               onclick='obs_alerts_dtable.hideColumn("site");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
    @endif
    @push('scripts')
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
@else
    <div style="text-align:center;margin:50px;">There are no patients to display</div>
@endif
