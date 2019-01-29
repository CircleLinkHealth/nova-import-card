@extends('partials.providerUI')

@section('title', 'All Patient Notes')

@section('content')

    <?php

     //Admins and Nurses have a complex role system which is regulated on the front end to minimize dev-time.
     //Both of them will see all notes ever that were forwarded if they check the "All Forwa..." checkmark.
     //Other users cannot see this. The regulation happens partly in NotesController@lisiting, which supplies
     //this view.

    if (isset($results)) {
        $webix = 'data:'.json_encode(array_values($results)).'';
    }

    ?>
    
    @push('scripts')
        <script>
            $(document).ready(function () {
                $(".provider-select").select2();
                $(".range-select").select2();

            });
        </script>
    @endpush

    <div class="row main-form-block" style="margin-top:30px;">
        <div class="main-form-container col-lg-8 col-lg-offset-2">
            <div class="row ">
                <div class="main-form-title col-lg-12">
                    All Patient Notes
                </div>
                {!! Form::open(array('url' => route('patient.note.listing'), 'method' => 'GET', 'class' => 'form-horizontal', 'style' => 'margin-right: 10px')) !!}
                <div style="clear:both"></div>
                <ul class="person-conditions-list inline-block pull-left">
                    <li class="inline-block"><input type="checkbox" id="mail_filter" name="mail_filter" value="true"
                        @if(isset($only_mailed_notes) && $only_mailed_notes == true)
                            {{'checked'}}
                                @endif>
                        <label for="mail_filter"><span> </span>Only Forwarded Notes <br /></label>
                    </li>
                    <li class=""><input type="checkbox" id="admin_filter" name="admin_filter" value="true"
                        @if(isset($admin_filter) && $admin_filter == true)
                            {{'checked'}}
                                @endif>
                        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('care-center') )
                            <label for="admin_filter"><span> </span>All Forwarded Notes for All Programs<br /></label>
                        @endif
                    </li>
                </ul>
                <div class="form-group pull-right" style="margin-top:10px; ">

                    <!--<span class="glyphicon glyphicon-user" aria-hidden="true"
                          style="color: #63bbe8; font-size: 28px; top: 0.4em;"></span>

                    <label for="provider" class="sr-only">Select Month:</label>

                    -->

                    <div class="inline-block">
                        <label for="year" class="sr-only">Date Range:</label>
                        <select name="range" id="range" class="range-select" data-width="250px">
                            <option value="">Select Range</option>
                            @for($i = 0; $i < 4; $i++)
                                <option value={{$i}}
                                @if(isset($dateFilter) && $dateFilter == $i)
                                    {{'selected'}}
                                        @endif
                                >Since {{\Carbon\Carbon::now()->subMonth($i)->format('F, Y')}}</option>
                            @endfor
                        </select>
                        <button type="submit" id="find" class="btn btn-primary">Go</button><br>
                        <select name="provider" id="provider" class="provider-select" data-width="200px"
                            data-size="10" style="display: none;" @if(auth()->user()->isAdmin() == false  &&
                                                          auth()->user()->hasRole('care-center') == false)
                            required
                            @endif>
                        <option value="" {{auth()->user()->isAdmin() ? 'selected' : ''}}>Select Provider</option>
                        @foreach($providers_for_blog as $key => $value)
                            @if(isset($selected_provider) && $selected_provider->id == $key)
                                <?php $selected = $selected_provider->display_name; ?>
                                <option value="{{$selected_provider->id}}"
                                        selected>{{$selected_provider->display_name}}</option>
                            @else
                                <option value={{$key}}>{{$value}}</option>
                            @endif
                        @endforeach
                    </select>
                    </div>
                </div>
                @push('scripts')
                    <script>
                        window.addEventListener('load', function () {
                            var rangeElem = document.querySelector("[name='range']")
                            rangeElem.style.display = 'block'
                            rangeElem.setAttribute('required', 'required')
                        });
                    </script>
                @endpush
                {!! Form::close() !!}


                <div class="main-form-horizontal main-form-primary-horizontal col-md-12"
                     style="border-top: 3px solid #50b2e2">
                    @if($isProviderSelected)
                        @if($notes)
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
                                    webix.ui.datafilter.mySummColumn = webix.extend({
                                        refresh: function (master, node, value) {
                                            var seconds = 0;
                                            master.data.each(function (obj) {
                                                seconds = seconds + parseInt(obj.duration);
                                            });
                                            var date = new Date(seconds * 1000);
                                            var mm = Math.floor(seconds / 60);
                                            var ss = date.getSeconds();
                                            if (ss < 10) {
                                                ss = "0" + ss;
                                            }
                                            var time = "" + mm + ":" + ss;
                                            result = "<span title='" + mm + ":" + ss + "' style='float:right;'><b>" + time + "</b></span>";
                                            node.firstChild.innerHTML = result;
                                        }
                                    }, webix.ui.datafilter.summColumn);

                                    obs_alerts_dtable = new webix.ui({
                                        container: "obs_alerts_container",
                                        view: "datatable",
                                        autoheight: true,
                                        fixedRowHeight: false, rowLineHeight: 25, rowHeight: 25,
                                        scrollX: true,
                                        resizeColumn: true,
                                        tooltip: true,
                                        footer: false,
                                        columns: [

                                            {
                                                id: "patient_name",
                                                header: ["Patient Name", {content: "textFilter", placeholder: "Filter"}],
                                                width: 140,
                                                sort: 'string',
                                                template: "<a href='<?php echo route('patient.note.view', [
                                                    'patient' => '#patient_id#',
                                                    'noteId'  => '#id#',
                                                ]); ?>'>#patient_name#</a>"


                                            },
                                            {
                                                id: "program_name",
                                                header: ["Program", {content: "selectFilter", placeholder: "Filter"}],
                                                width: 140,
                                                sort: 'string',
                                            },
                                            {
                                                id: "author_name",
                                                header: ["Author", {content: "selectFilter", placeholder: "Filter"}],
                                                width: 140,
                                                sort: 'string'
                                            },
                                            {
                                                id: "tags",
                                                css: {'text-align': 'left', 'top': 0, 'left': 0, 'bottom': 0, 'right': 0},
                                                header: ["Status", {content: "textFilter", placeholder: "Filter"}],
                                                width: 110,
                                                sort: 'string'
                                            },
                                            {
                                                id: "comment",
                                                header: ["Preview", {content: "textFilter", placeholder: "Filter"}],
                                                width: 250,
                                                sort: 'string',
                                                tooltip: ['#comment#'],
                                                fillspace: true,
                                                template: "<a href='<?php echo route('patient.note.view', [
                                                    'patient' => '#patient_id#',
                                                    'noteId'  => '#id#',
                                                ]); ?>'>#comment#</a>"
                                            },
                                            {
                                                id: "date",
                                                header: ["Date", {content: "textFilter", placeholder: "Filter"}],
                                                width: 110,
                                                sort: 'string'
                                            },
                                            {
                                                id: "type",
                                                header: ["Type", {content: "textFilter", placeholder: "Filter"}],
                                                width: 150,
                                                sort: 'string',
                                            },
                                        ],

                                        ready: function () {
                                            this.adjustRowHeight("obs_key");
                                        },

                                        pager: {
                                            container: "paging_container",// the container where the pager controls will be placed into
                                            template: "{common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
                                            size: 15, // the number of records per a page
                                            group: 5   // the number of pages in the pager
                                        },


                                        <?php echo $webix; ?>
                                    });


                                    obs_alerts_dtable.hideColumn("program_name");

                                    webix.event(window, "resize", function () {
                                        obs_alerts_dtable.adjust();
                                    });

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
                                        <div class="label label-success" style="margin-right: 4px; text-align: right;">
                                            <span class="glyphicon glyphicon-eye-open"></span>
                                        </div>
                                        Forward Seen By Provider
                                    </li>

                                </div>

                                <div class="col-sm-6">
                                    @if(auth()->user()->hasRole(['administrator', 'med_assistant', 'provider']))
                                        <input type="button" value="Export as Excel" class="btn btn-primary"
                                               style='margin:15px;'
                                               onclick="webix.toExcel($$(obs_alerts_dtable), {
                                                       header:'CarePlanManager.com - All Patient Notes @if(isset($selected_provider)) for {{$selected_provider->getFullName()}} @endif since <?=\Carbon\Carbon::now()->subMonth($dateFilter ?? 0)->format('F, Y'); ?>',
                                                       orientation:'landscape',
                                                       autowidth:true,
                                                       columns:{
                                                       'patient_name':       { header:'Patient Name', width: 200, template: webix.template('#patient_name#') },
                                                       'author_name':             { header:'Author Name',    width:200, sort:'string', template: webix.template('#author_name#')},
                                                       'comment':             { header:'Preview',    width:200, sort:'string', template: webix.template('#comment#')},
                                                       'type':             { header:'Type',    width:200, sort:'string', template: webix.template('#type#')},
                                                       'date':             { header:'Performed',    width:200, sort:'string', template: webix.template('#date#')},

                                                       }});">


                                        <input type="button" value="Export as PDF" class="btn btn-primary"
                                               style='margin:15px;'
                                               onclick="webix.toPDF($$(obs_alerts_dtable), {
                                                       header:'CarePlanManager.com - All Patient Notes @if(isset($selected_provider)) for {{$selected_provider->getFullName()}} @endif @if(isset($dateFilter)) since <?=\Carbon\Carbon::now()->subMonth($dateFilter)->format('F, Y'); ?> @endif',
                                                       orientation:'landscape',
                                                       autowidth:true,
                                                       columns:{
                                                       'patient_name':       { header:'Patient Name', width: 200, template: webix.template('#patient_name#') },
                                                       'author_name':             { header:'Author Name',    width:200, sort:'string', template: webix.template('#author_name#')},
                                                       'comment':             { header:'Preview',    width:200, sort:'string', template: webix.template('#comment#')},
                                                       'type':             { header:'Type',    width:200, sort:'string', template: webix.template('#type#')},
                                                       'date':             { header:'Performed',    width:200, sort:'string', template: webix.template('#date#')},

                                                       }});">
                                    @endif
                                    @if ( !Auth::guest() && Auth::user()->hasPermission(['admin-access']))
                                        <input id='site_show_btn' type='button' class='btn btn-primary'
                                               value='Show Program' style='margin:4px;'
                                               onclick='obs_alerts_dtable.showColumn("program_name");this.style.display = "none";getElementById("site_hide_btn").style.display = "inline-block";'>
                                        <input id='site_hide_btn' type='button' class='btn btn-primary'
                                               value='Hide Program' style='display:none;margin:4px;'
                                               onclick='obs_alerts_dtable.hideColumn("program_name");this.style.display = "none";getElementById("site_show_btn").style.display = "inline-block";'>
                                    @endif
                                </div>
                            </div>
                </div>
                @else
                    <div style="text-align:center;margin:50px;">There are no patients notes
                        for {{$selected_provider->display_name}} in input range.
                    </div>
                    <div>
                        @include('errors.errors')
                    </div>
                @endif
                @else
                    <div style="text-align:center;margin:50px;"><strong>Please select a Provider to view patient's
                            notes.</strong></div>
                @endif
            </div>
        </div>
    </div>
    </div>
@stop