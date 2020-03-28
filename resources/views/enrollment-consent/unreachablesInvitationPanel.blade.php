<div class="container">
    <div class="content">
        <ol>
            <li>
                <a href="https://app.moqups.com/veE6z3eVLS/view/page/aa9df7b72">Follow the flow chart here:</a>
                should be enough as test workflow guide (it's 2 pages):
            </li>

            <li>
                To re-create the process first:
                Click <strong>"Invite Patients to Enroll"</strong>. Will invite Initial AND Unreachable Patients for
                testing
            </li>
            <li>
                Check you email. You should have received all the invitations 2 initial plus 2 unreachable patients.
                I have setup the app to send everything to your email account.
            </li>
            <li>
                Same goes for the SMS....but i ll keep them disabled for first round of QA. Nothing changes in terms of
                wokflow
            </li>
        </ol>
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
                <a href="{{route('send.enrollment.invitations')}}">
                    <button type="button" class="btn btn-success">Invite Patients to Enroll</button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <a href="{{route('send.reminder.qa')}}">
                    <button type="button" class="btn btn-warning">Send Reminder to non responding patients (2 days after
                        first
                        invite)
                    </button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <a href="{{route('final.action.qa')}}">
                    <button type="button" class="btn btn-warning">Take Final Action on Non Reponding Patients (4 days
                        after
                        first invite)
                    </button>
                </a>
            </div>

            <br>

            <div class="request-info-href">
                <form action="{{route('evaluate.survey.completed')}}">
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
                    <a href="{{$data['invitationUrl']}}">Invitation for Unreachable Patient</a>
                </ul>
            @endif

        @endforeach
    </div>

</div>