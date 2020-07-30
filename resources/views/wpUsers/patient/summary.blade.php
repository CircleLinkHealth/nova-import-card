@extends('partials.providerUI')

@if (!empty($detailSection))
    @if ($detailSection == 'obs_biometrics')
        @section('title', 'Patient Summary - Biometrics Data Review')
@section('activity', 'Biometrics Data Review')
@endif

@if ($detailSection == 'obs_medications')
    @section('title', 'Patient Summary - Medications Data Review')
@section('activity', 'Medications Data Review')
@endif

@if ($detailSection == 'obs_symptoms')
    @section('title', 'Patient Summary - Symptoms Data Review')
@section('activity', 'Symptoms Data Review')
@endif

@if ($detailSection == 'obs_lifestyle')
    @section('title', 'Patient Summary - Lifestyle Data Review')
@section('activity', 'Lifestyle Data Review')
@endif
@else
    @section('title', 'Patient Summary Overview')
@section('activity', 'Patient Overview Review')
@endif

@section('content')
    <div class="row" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-10 col-xs-offset-1">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Overview
                </div>
                @include('partials.userheader')
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <?php
                    $filter   = '';
                    $sections = [
                        ['section'              => 'obs_biometrics',
                            'id'                => 'obs_biometrics_dtable',
                            'title'             => 'Biometrics',
                            'col_name_question' => 'Reading Type',
                            'col_name_severity' => 'Reading',
                        ],
                        ['section'              => 'obs_medications',
                            'id'                => 'obs_medications_dtable',
                            'title'             => 'Medications',
                            'col_name_question' => 'Medication',
                            'col_name_severity' => 'Adherence',
                        ],
                        ['section'              => 'obs_symptoms',
                            'id'                => 'obs_symptoms_dtable',
                            'title'             => 'Symptoms',
                            'col_name_question' => 'Symptom',
                            'col_name_severity' => 'Severity',
                        ],
                        ['section'              => 'obs_lifestyle',
                            'id'                => 'obs_lifestyle_dtable',
                            'title'             => 'Lifestyle',
                            'col_name_question' => 'Question',
                            'col_name_severity' => 'Response',
                        ],
                    ];
                    foreach ($sections as $section) {
                        if ( ! empty($detailSection)) {
                            if ($detailSection != $section['section']) {
                                continue 1;
                            }
                        } ?>
                    <div class="sub-form-title">
                        <div class="sub-form-title-lh"><?php echo $section['title']; ?></div>
                        <div class="sub-form-title-rh">
                            @push('styles')
                                <style type="text/css">
                                    i:hover {
                                        cursor: pointer;
                                    }
                                </style>
                            @endpush
                            <i class="fa fa-print" onclick="webix.toPDF($$({{ $section['id'] }}), {
                                    header:'CarePlanManager.com - Patient <?php echo $section['title']; ?> Report <?= date('M d,Y'); ?>',
                                    orientation:'landscape',
                                    autowidth:true,
                                    columns:{
                                    'description':       { header:'<?= $filter; ?>', width: 200, template: webix.template('#description#') },
                                    'obs_value':             { header:'<?= $section['col_name_severity']; ?>',    width:150, sort:'string', template: webix.template('#obs_value#')},
                                    'comment_date':    { header:'Date',    width:200, sort:'string', template: webix.template('#comment_date#') }
                                    }
                                    }
                                    );"></i> &nbsp;
                            <i class="fa fa-file-excel-o" onclick="webix.toExcel($$({{ $section['id'] }}), {
                                    columns:{
                                    'description':       { header:'<?= $filter; ?>', width: 200, template: webix.template('#description#') },
                                    'obs_value':             { header:'<?= $section['col_name_severity']; ?>',    width:150, sort:'string', template: webix.template('#obs_value#')},
                                    'comment_date':    { header:'Date',    width:200, sort:'string', template: webix.template('#comment_date#') }
                                    }
                                    }
                                    );"></i> &nbsp;

                            @if (!empty($detailSection))
                                <a href="{{route('patient.summary', ['patientId' => $wpUser->id])}}"><< Return</a>
                            @else
                                <a href="{{route('patient.summary', ['patientId' => $wpUser->id, 'user' => $wpUser->id, 'detail' => $section['section'] ])}}">Details
                                    >></a>
                            @endif

                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div id="<?php echo $section['id']; ?>" class="sub-form-indented-table"></div>
                    <br/>

                    <div id="paging_container"></div>
                    <br/>
                    @push('scripts')
                        <script>
                            function filterText(text) {
                                // var text = node;
                                if (!text) return;
                                <?php echo $section['id']; ?>.
                                filter();

                                <?php echo $section['id']; ?>.
                                filter(function (obj) {
                                    return obj.description == text;
                                })
                            }

                            webix.locale.pager = {
                                first: "<<",// the first button
                                last: ">>",// the last button
                                next: ">",// the next button
                                prev: "<"// the previous button
                            };
                            <?php echo $section['id']; ?>
                                = new webix.ui({
                                container:<?php echo $section['id']; ?>,
                                view: "datatable",
                                // css:"webix_clh_cf_style",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                // leftSplit:2,
                                scrollX: true,
                                resizeColumn: true,
                                //select:"row",
                                columns: [
                                    {
                                        id: "description",
                                        header: ["<?= $section['col_name_question']; ?>" <?= $filter; ?>],
                                        css: {"text-align": "left"},
                                        sort: 'string',
                                        width: 300,
                                        adjust: false
                                    },
                                    {
                                        id: "obs_value",
                                        header: ["<?= $section['col_name_severity']; ?>" <?= $filter; ?>],
                                        css: {"text-align": "left"},
                                        sort: 'string',
                                        width: 300,
                                        adjust: false,
                                        template: "<span class='label label-#dm_alert_level#'>#obs_value#</span>"
                                    },
                                    {
                                        id: "comment_date",
                                        header: ["Date" <?= $filter; ?>],
                                        css: {"text-align": "left"},
                                        sort: 'string',
                                        fillspace: true,
                                        width: 275,
                                        adjust: false,
                                    }
                                ],
                                ready: function () {
                                    this.adjustRowHeight("description");
                                    //CPM-725: Maximum Call Stack Size exceeded error on low-end machines
                                    this.config.autoheight = false;
                                },
                                <?php if ( ! empty($detailSection)) {
                            ?>
                                pager: {
                                    container: "paging_container",// the container where the pager controls will be placed into
                                    template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                    size: 10, // the number of records per a page
                                    group: 5   // the number of pages in the pager
                                },
                                <?php
                        } ?>
                                <?php echo $observation_data[$section['section']]; ?>
                                /*
                                 data:[{ obs_key:'Cigarettes', description:'Smoking (# per day)', obs_value:'8', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_50', comment_date:'09-04-15 06:43:56 PM', }, { obs_key:'Weight', description:'Weight', obs_value:'80', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_40', comment_date:'09-04-15 06:43:44 PM', }, { obs_key:'Weight', description:'Weight', obs_value:'80', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_40', comment_date:'09-02-15 09:11:14 PM', }, ],
                                 */
                            });

                            const <?php echo $section['id']; ?>Debounced = _.debounce(() => {
                                <?php echo $section['id']; ?>.adjust();
                            }, 1000);
                            webix.event(window, "resize", <?php echo $section['id']; ?>Debounced);
                        </script>
                    @endpush
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
@stop
