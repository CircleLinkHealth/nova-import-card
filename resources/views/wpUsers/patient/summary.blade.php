@extends('partials.providerUI')

@section('content')
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row">
                <div class="main-form-title col-lg-12">
                    Patient Overview
                </div>
                @include('partials.userheader');
                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12">
                    <?php
                    $filter = '';
                    $sections = array(
                            array('section' => 'obs_biometrics', 'id' => 'obs_biometrics_dtable', 'title' => 'Biometrics', 'col_name_question' => 'Reading Type', 'col_name_severity' => 'Reading'),
                            array('section' => 'obs_medications', 'id' => 'obs_medications_dtable', 'title' => 'Medications', 'col_name_question' => 'Medication', 'col_name_severity' => 'Adherence'),
                            array('section' => 'obs_symptoms', 'id' => 'obs_symptoms_dtable', 'title' => 'Symptoms', 'col_name_question' => 'Symptom', 'col_name_severity' => 'Severity'),
                            array('section' => 'obs_lifestyle', 'id' => 'obs_lifestyle_dtable', 'title' => 'Lifestyle', 'col_name_question' => 'Question', 'col_name_severity' => 'Response'),
                    );
                    foreach ($sections as $section) {
                    if (!empty($detailSection)) {
                        if ($detailSection != $section['section']) {
                            continue 1;
                        }
                    }
                    ?>
                    <div class="sub-form-title">
                        <div class="sub-form-title-lh"><?php echo $section['title']; ?></div>
                        <div class="sub-form-title-rh">
                            <?php
                            if (!empty($detailSection)) {
                                if ($section['section'] == 'obs_biometrics') {
                                    //echo '<a href="'.get_permalink( get_page_by_title('patient biometric chart') ).'?user='.$wpUser->ID.'"><span class="glyphicon glyphicon-stats"></span></a> &nbsp;&nbsp; ';
                                    echo '<a href="'.URL::route('patient.charts', array('patient' => $wpUser->ID)).'"><span class="glyphicon glyphicon-stats"></span></a> &nbsp;&nbsp;';
                                }
                                echo '<a href="?user=' . $wpUser->ID . '"><< Return</a>';
                            } else {
                                echo '<a href="?user=' . $wpUser->ID . '&detail=' . $section['section'] . '">Details >></a>';
                            }
                            ?>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div id="<?php echo $section['id']; ?>" class="sub-form-indented-table"></div>
                    <br/>

                    <div id="paging_container"></div>
                    <br/>
                    <script>
                        function filterText(text) {
                            // var text = node;
                            if (!text) return
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
                            scrollX: false,
                            resizeColumn: true,
                            //select:"row",
                            columns: [
                                {
                                    id: "description",
                                    header: ["<?= $section['col_name_question'] ?>" <?= $filter ?>],
                                    css: {"text-align": "center"},
                                    sort: 'string',
                                    width: 300,
                                    adjust: false
                                },
                                {
                                    id: "obs_value",
                                    header: ["<?= $section['col_name_severity'] ?>" <?= $filter ?>],
                                    css: {"text-align": "center"},
                                    sort: 'string',
                                    width: 300,
                                    adjust: false,
                                    template: "<span class='label label-#dm_alert_level#'>#obs_value#</span>"
                                },
                                {
                                    id: "comment_date",
                                    header: ["Date" <?= $filter ?>],
                                    css: {"text-align": "center"},
                                    sort: 'string',
                                    fillspace: true,
                                    adjust: true
                                }
                            ],
                            ready: function () {
                                this.adjustRowHeight("description");
                            },
                            <?php if(!empty($detailSection)) { ?>
                            pager: {
                                container: "paging_container",// the container where the pager controls will be placed into
                                template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                size: 10, // the number of records per a page
                                group: 5   // the number of pages in the pager
                            },
                            <?php } ?>
                            <?php echo $observation_data[$section['section']]; ?>
                            /*
                             data:[{ obs_key:'Cigarettes', description:'Smoking (# per day)', obs_value:'8', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_50', comment_date:'09-04-15 06:43:56 PM', }, { obs_key:'Weight', description:'Weight', obs_value:'80', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_40', comment_date:'09-04-15 06:43:44 PM', }, { obs_key:'Weight', description:'Weight', obs_value:'80', dm_alert_level:'default', obs_unit:'', obs_message_id:'CF_RPT_40', comment_date:'09-02-15 09:11:14 PM', }, ],
                             */
                        });
                        webix.event(window, "resize", function () { <?php echo $section['id']; ?>.
                            adjust();
                        })
                    </script>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
@stop