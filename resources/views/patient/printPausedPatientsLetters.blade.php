@extends('partials.providerUI')

@section('title', 'Patient CarePlan Print List')
@section('activity', 'Patient CarePlan Print List')

@section('content')

    <div class="container-fluid">
        <section class="main-form">
            <div class="row">
                <div class="">
                </div>
            </div>
            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <div class="main-form-title">Print Paused Patients Letters</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="main-form-container col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <div class="col-sm-2">
                            <h4 class="time-report__month"><?= date("F Y") ?></h4>
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
                                            template: "#first_name# #last_name#",
                                            width: 200,
                                            sort: 'string',
                                            adjust: true,
                                            fillspace: true
                                        },
                                        {
                                            id: "last_name",
                                            header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                                            template: "#last_name#, #first_name#",
                                            width: 200,
                                            sort: 'string',
                                            adjust: true,
                                            fillspace: true
                                        },
                                        {
                                            id: "link",
                                            header: ["Link to CarePlan"],
                                            template: "<a href='#link#' target='_blank'>View Careplan</a>",
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
                                        size: 20, // the number of records per a page
                                        group: 5   // the number of pages in the pager
                                    },
                                    on: {
                                        onSelectChange: function () {
                                            var text = paused_patients_letters_table.getSelectedId(true).join();
                                            var textmsg = "<a href='{!! URL::route('patients.careplan.multi')!!}?users=" + text + "&letter' class='btn btn-primary'>Print Selected</a>";
                                            document.getElementById('print_list').innerHTML = textmsg + '\n<BR>';
                                        }
                                    },
                                    data: {!! $patientJson !!}
                                });
                                webix.event(window, "resize", function () {
                                    paused_patients_letters_table.adjust();
                                }),
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