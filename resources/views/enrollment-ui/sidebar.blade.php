<ul style="width:25%; margin-top:65px;" class="side-nav fixed">
    <div class="col s12" style="width: 100%; padding: 0px 10px">
        <div class="card" style="background: rgb(24, 150, 24); font-size: 18px;">
            <div class="card-content white-text">
                <h5>Today's stats:</h5>
                <p>Total calls: {{$report->total_calls}}</p>
                <p>Patients enrolled: {{$report->no_enrolled}}</p>
                <p>Time worked: @{{formatted_total_time_in_system}}</p>
            </div>
        </div>
    </div>

    <span>
                    <li class="sidebar-demo-list"><span id="name"><b>Name: @{{name}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="name"><b>Provider Name: @{{provider_name}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="name"><b>Practice Name: @{{practice_name}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="home_phone"><b>Home Phone: @{{home_phone}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="cell_phone"><b>Cell Phone: @{{cell_phone}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="other_phone"><b>Other Phone: @{{other_phone}}</b></span></li>
                    <li class="sidebar-demo-list"><span id="email">Email: @{{email}}</span></li>
                    <li class="sidebar-demo-list"><span id="address">Address: @{{address}}</span></li>
                    <li class="sidebar-demo-list"><span id="address_2">Address Line 2: @{{address_2}}</span></li>
                    <li class="sidebar-demo-list"><span id="city">City: @{{city}}</span></li>
                    <li class="sidebar-demo-list"><span id="state">State: @{{state}}</span></li>
                    <li class="sidebar-demo-list"><span id="zip">Zip: @{{zip}}</span></li>
                    <li class="sidebar-demo-list"><span id="dob">DOB: @{{dob}}</span></li>
                 </span>

    <hr>

    <!--<li class="sidebar-demo-list"><span id="billing_provider">Dr. John Doe</span></li>-->

</ul>