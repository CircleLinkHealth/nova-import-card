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

                    @foreach ($sections as $section)
                        @if ( ! empty($detailSection))
                            @if ($detailSection != $section['section'])
                                @continue;
                            @endif
                        @endif

                    <div class="sub-form-title">
                        <div class="sub-form-title-lh">
                            {{$section['title']}}
                        </div>
                        <div class="sub-form-title-rh">
                            @push('styles')
                                <style type="text/css">
                                    i:hover {
                                        cursor: pointer;
                                    }
                                </style>
                            @endpush
                            <i class="fa fa-print" onclick="webix.toPDF($$({{ $section['id'] }}), {
                                    header:'CarePlanManager.com - Patient {{$section['title']}} Report {{date('M d,Y')}}',
                                    orientation:'landscape',
                                    autowidth:true,
                                    columns:{
                                    'description':       { header:'{{ $filter }}', width: 200, template: webix.template('#description#') },
                                    'obs_value':             { header:'{{$section['col_name_severity']}}',    width:150, sort:'string', template: webix.template('#obs_value#')},
                                    'comment_date':    { header:'Date',    width:200, sort:'string', template: webix.template('#comment_date#') }
                                    }
                                    }
                                    );"></i> &nbsp;
                            <i class="fa fa-file-excel-o" onclick="webix.toExcel($$({{ $section['id'] }}), {
                                    columns:{
                                    'description':       { header:'{{ $filter }}', width: 200, template: webix.template('#description#') },
                                    'obs_value':             { header:'{{$section['col_name_severity']}}',    width:150, sort:'string', template: webix.template('#obs_value#')},
                                    'comment_date':    { header:'Date',    width:200, sort:'string', template: webix.template('#comment_date#') }
                                    }
                                    }
                                    );"></i> &nbsp;

                            @if (!empty($detailSection))
                                @if ($section['section'] == 'obs_biometrics')
                                    <a href="{{ route('patient.charts', ['patientId' => $wpUser->id]) }}"><span
                                                class="glyphicon glyphicon-stats"></span></a> &nbsp;&nbsp;
                                @endif
                                <a href="{{route('patient.summary', ['patientId' => $wpUser->id])}}"><< Return</a>
                            @else
                                <a href="{{route('patient.summary', ['patientId' => $wpUser->id, 'user' => $wpUser->id, 'detail' => $section['section'] ])}}">Details
                                    >></a>
                            @endif

                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div id="{{$section['id']}}" class="sub-form-indented-table"></div>
                    <br/>

                    <div id="paging_container"></div>
                    <br/>
                    @push('scripts')
                        <script>
                            function filterText(text) {
                                // var text = node;
                                if (!text) return;
                                {{$section['id']}}.
                                filter();

                                    {{$section['id']}}.
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
                                    {{$section['id']}}
                                = new webix.ui({
                                container:{{$section['id']}},
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
                                        header: ["{{$section['col_name_question']}}" {{ $filter }}],
                                        css: {"text-align": "left"},
                                        sort: 'string',
                                        width: 300,
                                        adjust: false
                                    },
                                    {
                                        id: "obs_value",
                                        header: ["{{$section['col_name_severity']}}" {{ $filter }}],
                                        css: {"text-align": "left"},
                                        sort: 'string',
                                        width: 300,
                                        adjust: false,
                                        template: "<span class='label label-#dm_alert_level#'>#obs_value#</span>"
                                    },
                                    {
                                        id: "comment_date",
                                        header: ["Date" {{ $filter }}],
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
                                @if ( ! empty($detailSection))
                                    pager: {
                                        container: "paging_container",// the container where the pager controls will be placed into
                                        template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                        size: 10, // the number of records per a page
                                        group: 5   // the number of pages in the pager
                                    },
                                @endif

                            {!! 'data:'.$observation_data[$section['section']] !!}
                            });

                            const {{$section['id']}}Debounced = _.debounce(() => {
                                {{$section['id']}}.adjust();
                            }, 1000);
                            webix.event(window, "resize", {{$section['id']}}Debounced);
                        </script>
                    @endpush
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@stop
