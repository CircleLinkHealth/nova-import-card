<ul style="width:25%; margin-top:65px;" class="side-nav fixed">
    <div class="col s12" style="width: 100%; padding: 0px 10px">
        <div class="card" style="background: rgb(24, 150, 24); font-size: 18px;">
            <div class="card-content white-text">
                <p>You’ve done {{$report->total_calls}} calls today and
                    enrolled {{$report->no_enrolled}} patients.</p> <br />
                <p>Time worked today: @{{formatted_total_time_in_system}}</p>
            </div>
        </div>
    </div>

    <span>
                    <li class="sidebar-demo-list"><span id="name">Name: @{{name}}</span></li>
                    <li class="sidebar-demo-list"><span id="name">Provider Name: @{{provider_name}}</span></li>
                    <li class="sidebar-demo-list"><span id="cell_phone">Primary Phone: @{{primary_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Home Phone: @{{home_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Cell Phone: @{{cell_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Other Phone: @{{other_phone}}</span></li>
                    <li class="sidebar-demo-list"><span id="home_phone">Email: @{{email}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Address: @{{address}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Address Line 2: @{{address_2}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">City: @{{city}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">State: @{{state}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Zip: @{{zip}}</span></li>
                    <li class="sidebar-demo-list"><span id="dob">DOB: @{{dob}}</span></li>
                 </span>

    <hr>

    <!--<li class="sidebar-demo-list"><span id="billing_provider">Dr. John Doe</span></li>-->

</ul>