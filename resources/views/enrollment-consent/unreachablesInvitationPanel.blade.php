<div class="buttons col-lg-12">
    <div class="row">
        <div class="enroll-now-href">
            <label>Will invite initial patients + Unreachable Patients to work with</label><br>
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
                <button type="button" class="btn btn-warning">Take Final Action on Non Reponding Patients (4 days after
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

<style>

</style>