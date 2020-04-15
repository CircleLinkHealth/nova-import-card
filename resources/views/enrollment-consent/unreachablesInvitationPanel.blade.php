<div class="container">
    <div class="content">
        <ul>
            <li>
                <a href="https://app.moqups.com/veE6z3eVLS/view/page/aa9df7b72" target="_blank">Follow this flow chart</a>
                should be enough as test workflow guide (it's 2 pages):
            </li>

            <li>
                Click <strong>"Invite Patients to Enroll"</strong> to invite 2 Initial AND 2 Unreachable Patients for
                testing.
            </li>
            <li>
                Check you email. You should have received all the invitations 2 initial plus 2 unreachable patients.
                I have setup the app to send everything to your email account.
            </li>
            <li>
                Same goes for the SMS but they are disabled for first round of QA.
            </li>
        </ul>
        <ul>
            <li>
                Note: if you click <strong>"Invite Patients to Enroll"</strong> and take no action (enroll now or
                request info), then you can
                fast forward 2 days after the first invitation to send the FIRST reminder to non responsive patients.
            </li>
            <li>
                Only AFTER you clicked the <strong>"Send Reminder to non responding patients (2 days after first
                    invite)"</strong>, then you can fast forward 4 days to
                test the button "Final Action on Non..."
            </li>
            <li>
                You can reset and recreate the test unlimited times
            </li>
        </ul>
    </div>
    <div class="buttons col-lg-12">
        <div class="row">
            <div class="enroll-now-href">
                <h4>Step 1</h4>
                <a href="{{route('trigger.enrolldata.test')}}">
                    <button type="button" class="btn btn-success">Create Test Conditions first</button>
                </a>
            </div>

            <br>

            <div class="enroll-now-href">
                <h4>Step 2</h4>
                <a href="{{route('send.enrollment.invitations')}}">
                    <button type="button" class="btn btn-success">Invite Patients to Enroll</button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <h4>Test Case 1</h4>
                <a href="{{route('send.reminder.qa')}}" target="_blank">
                    <button type="button" class="btn btn-warning">Send Reminder to non responding patients (2 days after
                        first invitation)
                    </button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <h4>Test Case 2</h4>
                <a href="{{route('final.action.qa')}}" target="_blank">
                    <button type="button" class="btn btn-warning">Take Final Action on Non Reponding Patients (4 days
                        after first invite)
                    </button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <h4>Test Case 3 - User Completed Enrollment Survey</h4>
                <form action="{{route('evaluate.survey.completed')}}" target="_blank">
                    <label for="enrolleeId">Enter ID of patient that Completed Survey(Enrolled)
                        - Only use if did not import automatically after survey completion:</label><br>
                    <input type="text" id="enrolleeId" name="enrolleeId" placeholder="ex.1616"><br>
                    <input type="submit" value="Submit">
                </form>
            </div>

            <div class="request-info-href">
                <a href="{{route('reset.test.qa')}}">
                    <button type="button" class="btn btn-warning">Reset Test
                    </button>
                </a>
            </div>
        </div>
    </div>

    <div>
        @foreach($invitationData as $data)
            @if($data['isEnrolleeClass'])
                <ul>
                    Invited Name: {{$data['name']}}
                    <br>
                    DOB: {{$data['dob']}}
                    <br>
                    <a href="{{$data['invitationUrl']}}">Invitation for Enrollee</a>
                </ul>
            @else
                <ul>
                    Invited Name: {{$data['name']}}
                    <br>
                    DOB: {{$data['dob']}}
                    <br>
                    <a href="{{$data['invitationUrl']}}" target="_blank">Invitation for Unreachable Patient</a>
                </ul>
            @endif

        @endforeach
    </div>

</div>