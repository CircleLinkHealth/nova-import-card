@extends('partials.providerUI')

@section('title', 'Patient Activity Report')
@section('activity', 'Patient Activity Report')

@section('content')
    @push('styles')
        <style>
            .duration-footer {
                float: right;
                text-align: right;
                font-weight: bold;
            }
        </style>
    @endpush
    <div class="row" style="margin-top:60px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2 col-xs-10 col-xs-offset-1">
            <div class="row">
                <div class="main-form-title">
                    Patient Activity Report
                </div>
                @include('partials.userheader')

                {!! Form::open(array('url' => route('patient.activity.providerUIIndex',
                ['patientId' => $patient->id]),
                'method' => 'GET',
                'class' => 'form-horizontal',
                'style' => 'margin-right: 10px',
                'id' => 'patient-activities-date-form',
                )) !!}

                <div class="col-sm-3 col-xs-3" style="top: 20px">
                    <input type="submit" value="Audit Report" name="download-audit-report" id="download-audit-report" class="btn btn-primary">

                @if ($data && $month_selected_text === \Carbon\Carbon::now()->format('F'))
                        <button id="refresh-activity" type="button" class="btn btn-primary">
                            Reload Table
                        </button>

                        <div style="left: 250px; right: 0px;" class="loader-container">
                            <div id="refresh-activity-loader" class="loader" style="display: none"></div>
                        </div>
                    @endif

                </div>
                <div class="form-group pull-right col-xs-7" style="margin-top:10px; ">
                    <i class="icon icon--date-time hidden-xs"></i>
                    <div class="inline-block">
                        <label for="selectMonth" class="sr-only">Select Month:</label>
                        <select name="selectMonth" id="selectMonth" class="selectpicker" data-width="160px"
                                data-size="10" style="display: none;">
                            <option value="">Select Month</option>
                            @for($i = 0; $i < count($months); $i++)
                                <option value="{{$i+1}}" @if($month_selected == $i+1) {{'selected'}} @endif>{{$months[$i]}}</option>
                            @endfor

                        </select>

                        <div class="inline-block">
                            <label for="selectYear" class="sr-only">Select Year:</label>
                            <select name="selectYear" id="selectYear" class="selectpicker" data-width="100px"
                                    data-size="10" style="display: none;">
                                @foreach($years as $year)
                                    <option value="{{$year}}" @if($year_selected == $year) {{'selected'}} @endif>{{$year}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input form="patient-activities-date-form" type="submit" value="Go" name="find" id="find" class="btn btn-primary">

                        <a id="downloadAudit" href="" hidden></a>

                    </div>
                </div>
                {!! Form::close() !!}

                <div class="main-form-block main-form-horizontal main-form-primary-horizontal col-md-12 col-xs-12"
                     style="border-top: 3px solid #50b2e2">
                    @if($data)
                        <div id="obs_alerts_container" class=""></div><br/>
                        <div id="paging_container"></div><br/>
                        @push('styles')
                            <style>
                                .webix_hcell {
                                    background-color: #d2e3ef;
                                }
                            </style>
                        @endpush
                        @push('scripts')

                            <script>

                                const patientId = {{$patient->id}};

                                function startCompare(value, filter) {
                                    value = value.toString().toLowerCase();
                                    filter = '<' + filter.toString().toLowerCase();
                                    return value.indexOf(filter) === 0;
                                }

                                function durationType(obj) {
                                    return obj.is_behavioral ? 'BHI' : 'CCM';
                                }

                                function durationSumm(master, type) {
                                    var seconds = 0;
                                    master.data.each(function (obj) {
                                        if (durationType(obj) == type) {
                                            seconds = seconds + parseInt(obj.duration);
                                        }
                                    });
                                    var date = new Date(seconds * 1000);
                                    var hh = Math.floor(seconds / 3600);
                                    var mm = Math.floor(seconds / 60) % 60;
                                    var ss = date.getSeconds();

                                    function pad(num, count) {
                                        count = count || 0;
                                        const $num = num + '';
                                        return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
                                    }

                                    ss = pad(ss, 2)
                                    mm = pad(mm, 2)
                                    hh = pad(hh, 2)
                                    var time = hh + ':' + mm + ":" + ss;
                                    return time;
                                }

                                function durationData(obj, type) {
                                    if (durationType(obj) === type) {
                                        var seconds = obj.duration;
                                        var date = new Date(seconds * 1000);
                                        var hh = Math.floor(seconds / 3600);
                                        var mm = Math.floor(seconds / 60) % 60;
                                        var ss = date.getSeconds();

                                        function pad(num, count) {
                                            count = count || 0;
                                            const $num = num + '';
                                            return '0'.repeat(Math.max(count - $num.length, 0)) + $num;
                                        }

                                        ss = pad(ss, 2)
                                        mm = pad(mm, 2)
                                        hh = pad(hh, 2)

                                        var time = hh + ':' + mm + ":" + ss;

                                        return time;
                                    } else {
                                        return "--";
                                    }
                                }

                                webix.locale.pager = {
                                    first: "<<",// the first button
                                    last: ">>",// the last button
                                    next: ">",// the next button
                                    prev: "<"// the previous button
                                };
                                webix.ui.datafilter.mySummColumnCCM = webix.extend({
                                    refresh: function (master, node, value) {
                                        node.firstChild.innerHTML = durationSumm(master, 'CCM');
                                    }
                                }, webix.ui.datafilter.summColumn);
                                webix.ui.datafilter.mySummColumnBHI = webix.extend({
                                    refresh: function (master, node, value) {
                                        node.firstChild.innerHTML = durationSumm(master, 'BHI');
                                    }
                                }, webix.ui.datafilter.summColumn);

                            obs_alerts_dtable = new webix.ui({
                                container: "obs_alerts_container",
                                view: "datatable",
                                //css:"webix_clh_cf_style",
                                autoheight: true,
                                fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                // leftSplit:2,
                                scrollX: true,
                                resizeColumn: true,
                                footer: true,
                                columns: [
                                    {
                                        id: "performed_at",
                                        header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                        footer: {text: "Total Time for the Month (Min:Sec):", colspan: 3},
                                        width: 180,
                                        sort: 'string'
                                    },
                                    {
                                        id: "type",
                                        header: ["Activity", {content: "textFilter", placeholder: "Filter"}],

                                            template: function (obj) {
                                                if (obj.logged_from == "manual_input" || obj.logged_from == "activity")
                                                    return `<a href="/manage-patients/${patientId}/view/${obj.id}">${obj.type}</a>`;
                                                else
                                                    return obj.type;
                                            },

                                            fillspace: true,
                                            width: 202,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "left"}
                                        },

                                        {
                                            id: "provider_name",
                                            header: ["Provider", {content: "textFilter", placeholder: "Filter"}],
                                            width: 220,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "right"}
                                        },
                                        {
                                            id: "durationCCM",
                                            header: ["Total CCM", "(HH:MM:SS)"],
                                            width: 150,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "right"},
                                            footer: {content: "mySummColumnCCM", css: "duration-footer"},
                                            template: function (obj) {
                                                return durationData(obj, 'CCM');
                                            }
                                        },
                                        {
                                            id: "durationBHI",
                                            header: ["Total BHI", "(HH:MM:SS)"],
                                            width: 150,
                                            fillspace: true,
                                            sort: 'string',
                                            css: {"color": "black", "text-align": "right"},
                                            footer: {content: "mySummColumnBHI", css: "duration-footer"},
                                            template: function (obj) {
                                                return durationData(obj, 'BHI');
                                            }
                                        }
                                    ],
                                    ready: function () {
                                        this.adjustRowHeight("type");
                                        //CPM-725: Maximum Call Stack Size exceeded error on low-end machines
                                        this.config.autoheight = false;
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
                                    {!! $activity_json !!}                         });

                                const debounced = _.debounce(() => {
                                    obs_alerts_dtable.adjust();
                                }, 1000);
                                webix.event(window, "resize", debounced);

                                $('#refresh-activity').click(function () {

                                    $('#refresh-activity').prop('disabled', true);
                                    $('#refresh-activity-loader').show();

                                    const url = '{!! route('patient.activity.get.current.for.patient', ['patientId' => $patient->id]) !!}';

                                    $.ajax({
                                        type: "GET",
                                        url: url,
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: {},
                                        success: function (data) {
                                            $('#monthly-time-static').html(data.monthlyTime);
                                            $('#monthly-bhi-time-static').html(data.monthlyBhiTime);
                                            const scrollPosition = $(document).scrollTop();
                                            obs_alerts_dtable.clearAll();
                                            obs_alerts_dtable.parse(data.table);
                                            $(document).scrollTop(scrollPosition);
                                        },
                                        complete: function () {
                                            $('#refresh-activity').prop('disabled', false);
                                            $('#refresh-activity-loader').hide();
                                        }

                                    });

                                    return false;
                                });

                            </script>
                        @endpush
                        @if(auth()->user()->hasRole(array_merge(['administrator'], \App\Constants::PRACTICE_STAFF_ROLE_NAMES)))
                            <input type="button" value="Export as PDF" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toPDF($$(obs_alerts_dtable), {
                                           header:'CarePlanManager.com - Patient Activity Report <?= date('M d,Y'); ?>',
                                           orientation:'landscape',
                                           autowidth:true,
                                           columns:{
                                           'performed_at':       { header:'Date', width: 200, template: webix.template('#performed_at#') },
                                           'type':             { header:'Activity',    width:150, sort:'string', template: webix.template('#type#')},
                                           'provider_name':    { header:'Provider',    width:200, sort:'string', template: webix.template('#provider_name#') },
                                           'durationCCM':  { header: 'Total CCM (Min:Sec)', width: 70, sort: 'string',
                                           template: function (obj) {
                                           var type = durationType(obj);
                                           if (type === 'CCM'){
                                           var seconds = obj.duration;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           if (ss < 10) {ss = '0'+ss;}
                                           var time = mm+':'+ss;
                                           return mm+':'+ss;
                                           }else {
                                           return '--';
                                           }
                                           }
                                           },
                                           'durationBHI':  { header: 'Total BHI (Min:Sec)', width: 70, sort: 'string',
                                           template: function (obj) {
                                           var type = durationType(obj);
                                           if (type === 'BHI'){
                                           var seconds = obj.duration;
                                           var date = new Date(seconds * 1000);
                                           var mm = Math.floor(seconds/60);
                                           var ss = date.getSeconds();
                                           if (ss < 10) {ss = '0'+ss;}
                                           var time = mm+':'+ss;
                                           return mm+':'+ss;
                                           }else {
                                           return '--';
                                           }
                                           }
                                           }
                                           }
                                           });">
                            <input type="button" value="Export as Excel" class="btn btn-primary" style='margin:15px;'
                                   onclick="webix.toExcel(obs_alerts_dtable);">
                        @endif
                    @else
                        <div style="text-align:center;margin:50px;">There are no patient activities to display for this
                            month.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div></div>

    @push('scripts')
        <script>

            @if ($data && $month_selected_text === \Carbon\Carbon::now()->format('F'))
                setInterval(function () {
                    $('#refresh-activity').click();
                }, 5000);
            @endif
        </script>
    @endpush
@stop
