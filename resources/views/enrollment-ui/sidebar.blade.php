<ul style="width:20%; margin-top:65px;" class="side-nav fixed">

    <div class="row">
        <div class="col s6">
            <div class="card">
                <div class="card-content" style="text-align: center">
                    <div style="color: #6d96c5" class="counter">
                        {{$report->total_calls ?? 0}}
                    </div>
                    <div class="card-subtitle">
                        Total Calls
                    </div>
                </div>
            </div>
        </div>

        <div class="col s6">
            <div class="card">
                <div class="card-content" style="text-align: center">
                    <div style="color: #9fd05f" class="counter">
                        {{$report->no_enrolled ?? 0}}
                    </div>
                    <div class="card-subtitle">
                        Enrolled
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content" style="text-align: center">
                    <div style="color: #6d96c5" class="counter">
                        @{{formatted_total_time_in_system}}
                    </div>
                    <div class="card-subtitle">
                        Time worked
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <ul>
                        <li class="sidebar-demo-list"><span id="name"><b>Name:</b> @{{name}}</span></li>
                        <li class="sidebar-demo-list"><span id="name"><b>Language:</b> @{{ lang }}</span></li>
                        <li class="sidebar-demo-list"><span id="name"><b>Provider Name:</b> @{{provider_name}}</span>
                        </li>
                        <li class="sidebar-demo-list"><span id="name"><b>Practice Name:</b> @{{practice_name}}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <div class="row">

                        <div v-if="callError">
                            <blockquote>Call Status: @{{ callError }}</blockquote>
                        </div>

                        <div v-if="onCall === true" style="text-align: center">

                            <blockquote>Call Status: @{{ this.callStatus }}</blockquote>
                            <a v-on:click="hangUp" class="waves-effect waves-light btn" style="background: red"><i
                                        class="material-icons left">call_end</i>Hang Up</a>
                        </div>
                        <div v-else style="text-align: center">
                            @if($enrollee->home_phone_e164 != '')
                                <div class="col s4">

                                    <div class="waves-effect waves-light btn call-button"
                                         v-on:click="call(home_phone, 'Home')">
                                        <i class="material-icons">phone</i>
                                    </div>
                                    <div>
                                        Home
                                    </div>

                                </div>
                            @endif
                            @if($enrollee->cell_phone_e164 != '')
                                <div class="col s4">

                                    <div class="waves-effect waves-light btn call-button"
                                         v-on:click="call(cell_phone, 'Cell')">
                                        <i class="material-icons">phone</i>

                                    </div>
                                    <div>
                                        Cell
                                    </div>

                                </div>
                            @endif
                            @if($enrollee->other_phone_e164 != '')
                                <div class="col s4">

                                    <div class="waves-effect waves-light btn call-button"
                                         v-on:click="call(other_phone, 'Other')">
                                        <i class="material-icons">phone</i>

                                    </div>

                                    <div>
                                        Other
                                    </div>


                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card">
                <div class="card-content">
                    <ul>
                        <li>
                            <a class="waves-effect waves-light btn" href="#consented">
                                Consented
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect waves-light btn" href="#utc" style="background: #ecb70e">
                                No Answer
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect waves-light btn" href="#rejected" style="background: red;">
                                Hard Declined
                            </a>
                        </li>
                        <li>
                            <a class="waves-effect waves-light btn" href="#rejected"
                               v-on:click="softReject()"
                               style="background: #ff0000c2;">
                                Soft Declined
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</ul>