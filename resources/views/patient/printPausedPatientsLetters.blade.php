@extends('partials.providerUI')

@section('title', 'Print Paused Patients Letters')
@section('activity', 'Print Paused Patients Letters')

@section('content')

    <div class="container-fluid">
        <section class="main-form">
            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <div class="main-form-title">Print Paused Patients' Letters</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row" style="border-bottom: #50b2e2 3px solid;">
                        @if($patients)
                            <div id="paused_patients_letters_container" class=""></div><br/>
                            <div id="paging_container"></div><br/>
                            @push('scripts')
                            <script>
                                function getReportColumns() {
                                    return {
                                        'first_name': {
                                            header: 'Patient Name',
                                            width: 200,
                                            template: webix.template('#first_name# #last_name#')
                                        },
                                        'provider': {
                                            header: 'Provider',
                                            width: 200,
                                            sort: 'string',
                                            template: webix.template('#provider#')
                                        },
                                        'program_name': {
                                            header: 'Program',
                                            width: 150,
                                            sort: 'string',
                                            template: webix.template('#program_name#')
                                        },
                                        'reg_date': {
                                            header: 'Registered On',
                                            width: 120,
                                            sort: 'string',
                                            template: webix.template('#reg_date#')
                                        }
                                    }
                                }

                                function toPdf() {
                                    return webix.toPDF($$(paused_patients_letters_table), {
                                        header: 'CarePlanManager.com - Patient CarePlan Print List',
                                        orientation: 'landscape',
                                        autowidth: true,
                                        columns: getReportColumns()
                                    });
                                }

                                function toExcel() {
                                    return webix.toExcel($$(paused_patients_letters_table), {columns: getReportColumns()});
                                }

                                function showByLastName() {
                                    paused_patients_letters_table.showColumn("last_name");
                                    paused_patients_letters_table.hideColumn("first_name");
                                    paused_patients_letters_table.sort("#last_name#");
                                    $("#lastName_btn").hide();
                                    $("#firstName_btn").css('display', 'inline-block');
                                }

                                function showByFirstName() {
                                    paused_patients_letters_table.showColumn("first_name");
                                    paused_patients_letters_table.hideColumn("last_name");
                                    paused_patients_letters_table.sort("#first_name#");
                                    $("#lastName_btn").css('display', 'inline-block');
                                    $("#firstName_btn").hide();
                                }

                                webix.locale.pager = {
                                    first: "<<",// the first button
                                    last: ">>",// the last button
                                    next: ">",// the next button
                                    prev: "<"// the previous button
                                };
                                paused_patients_letters_table = new webix.ui({
                                    container: "paused_patients_letters_container",
                                    view: "datatable",
                                    autoheight: true,
                                    fixedRowHeight: false,
                                    rowLineHeight: 25,
                                    rowHeight: 25,
                                    scrollX: false,
                                    resizeColumn: true,
                                    select: "row",
                                    multiselect: true,
                                    blockselect: true,
                                    columns: [
                                        {
                                            id: "first_name",
                                            header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                                            template: "<a href='#link#' target='_blank'>#first_name# #last_name#</a>",
                                            width: 200,
                                            sort: 'string',
                                            adjust: true,
                                            fillspace: true
                                        },
                                        {
                                            id: "last_name",
                                            header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                                            template: "<a href='#link#' target='_blank'>#last_name#, #first_name#</a>",
                                            width: 200,
                                            sort: 'string',
                                            adjust: true,
                                            fillspace: true
                                        },
                                        {
                                            id: "link",
                                            header: ["Link to Preview Letter"],
                                            template: "<a href='{!! $url !!}#id#&view' target='_blank'>Preview Letter</a>",
                                            width: 200,
                                        },
                                        {
                                            id: "program_name",
                                            header: ["Program", {content: "selectFilter", placeholder: "Filter"}],
                                            width: 200,
                                            sort: 'string'
                                        },
                                        {
                                            id: "provider",
                                            header: ["Provider", {content: "selectFilter", placeholder: "Filter"}],
                                            width: 200,
                                            sort: 'string'
                                        },
                                        {
                                            id: "reg_date",
                                            header: ["Registered On", {content: "dateFilter", placeholder: "Filter"}],
                                            width: 200,
                                            sort: 'string',
                                        },
                                        {
                                            id: "paused_date",
                                            header: ["Paused On", {content: "dateFilter", placeholder: "Filter"}],
                                            width: 200,
                                            sort: 'string',
                                        },

                                    ],
                                    pager: {
                                        animate: true,
                                        container: "paging_container",// the container where the pager controls will be placed into
                                        template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                        size: 10, // the number of records per a page
                                        group: 5   // the number of pages in the pager
                                    },
                                    ready: function () {
                                        //CPM-725: Maximum Call Stack Size exceeded error on low-end machines
                                        this.config.autoheight = false;
                                    },
                                    on: {
                                        onSelectChange: function () {
                                            var text = paused_patients_letters_table.getSelectedId(true).join();
                                            var textmsg = "<a href='{!! $url !!}" + text + "' class='btn btn-primary'>Print Selected</a>";
                                            document.getElementById('print_selected_btn_container').innerHTML = textmsg;
                                        }
                                    },
                                    data: {!! $patients !!}
                                });
                                const debounced = _.debounce(() => {
                                    paused_patients_letters_table.adjust();
                                }, 1000);
                                webix.event(window, "resize", debounced);
                                paused_patients_letters_table.sort("#patient_name#");
                                paused_patients_letters_table.hideColumn("last_name");
                            </script>
                            @endpush

                            <input id="lastName_btn" type='button' class='btn btn-primary' value='Show by Last Name'
                                   style='margin:15px;'
                                   onclick="showByLastName()">

                            <input id="firstName_btn" type='button' class='btn btn-primary' value='Show by First Name'
                                   style='display:none;margin:15px;'
                                   onclick="showByFirstName()">
                            @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))
                                <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                                       onclick="toPdf()">
                                <input type="button" value="Export as Excel" class="btn btn-primary"
                                       style='margin:15px;'
                                       onclick="toExcel()">
                            @endif

                            <span id="print_selected_btn_container"></span>
                        @else
                            <div style="text-align:center;margin:50px;">There are no patients to display</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
