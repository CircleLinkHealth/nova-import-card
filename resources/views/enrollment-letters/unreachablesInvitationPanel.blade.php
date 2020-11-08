@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Enrollment Test Panel')
@section('activity', 'Enrollment Test Panel')
@section('content')
<div class="container">
    <div class="content">
        @if(session()->has('message'))
            <div class="alert alert-info">
                {{session()->get('message')}}
            </div>
        @endif

        <h4 style="text-align: center">Test Dashboard</h4>
        <ul class="browser-default">
            <li>
                <a href="https://app.moqups.com/veE6z3eVLS/view/page/aa9df7b72" target="_blank">You should follow this flow
                    chart</a>
               as guide (it's 2 pages):
            </li>
        </ul>
    </div>
    <div class="buttons col-lg-12">
  <div class="row">
       <form action="{{route('trigger.enrolldata.test')}}" method="GET">
           <div>
               <div class="input-field col s12">
                   <select id="practice-select" name="practice-select" style="display: inline-block">
                       <option value="" disabled selected>Choose practice to create data for</option>
                       <option value="toledo-clinic">Toledo Clinic</option>
                       <option value="commonwealth-pain-associates-pllc">Commonwealth Clinic</option>
                       <option value="calvary-medical-clinic">Calvary Clinic</option>
                       <option value="woodlands-internists-pa">Woodlands Clinic</option>
                       <option value="davis-county">Davis County Clinic</option>
                       <option value="marillac-clinic">Marillac Health</option>
                   </select>
               </div>
               <a>
                   <button class="btn waves-effect waves-light" type="submit">Create Test Patients</button>
               </a>
               <ul class="browser-default">
                   <li>
                       Emulates Enrollees Imported from CSV
                       <br> and ready to be invited for Auto Enrollment.
                   </li>
               </ul>

           </div>
       </form>

          <br>

  </div>
  </div>
{{--        <div class="row">--}}
{{--            <div style="display: inline-flex;">--}}
{{--          <div class="request-info-href">--}}
{{--              <h5>Test Case 1.</h5>--}}
{{--              <p>Send 1st. Reminder to non responding patients</p>--}}
{{--              <ul class="browser-default">--}}
{{--                  <li>--}}
{{--                      For this to work,<br> you should just invite test patients and take no action after.--}}
{{--                  </li>--}}
{{--              </ul>--}}
{{--            <div>--}}
{{--                <a href="{{route('send.reminder.enrollee.qa')}}" target="_blank">--}}
{{--                    <button type="button" class="btn btn-warning">--}}
{{--                        Fast Forward 2 days and send reminders to Enrollees--}}
{{--                    </button>--}}
{{--                </a>--}}
{{--            </div>--}}
{{--<br>--}}
{{--             <div>--}}
{{--                 <a href="{{route('send.reminder.patient.qa')}}" target="_blank">--}}
{{--                     <button type="button" class="btn btn-warning">--}}
{{--                         Fast Forward 2 days and send reminders to Patients--}}
{{--                     </button>--}}
{{--                 </a>--}}
{{--             </div>--}}
{{--          </div>--}}

{{--          <br>--}}

{{--          <div class="request-info-href">--}}
{{--              <h5>Test Case 2</h5>--}}
{{--              <p>Take Final Action on Non Responding Patients</p>--}}
{{--              <ul class="browser-default">--}}
{{--                  <li>--}}
{{--                      For this to work, you should just invite test patients and take no action after.--}}
{{--                  </li>--}}
{{--                  <li>--}}
{{--                      Should go through "Test Case 1." first--}}
{{--                  </li>--}}
{{--              </ul>--}}
{{--              <a href="{{route('final.action.qa')}}" target="_blank">--}}
{{--                  <button type="button" class="btn btn-warning">--}}
{{--                      Fast forward 4 days. Final Step<br>--}}
{{--                  </button>--}}
{{--              </a>--}}
{{--              <p> After Final Step is done, you will be redirected to "Care Ambassador Director Panel"<br>--}}
{{--                  You can check if patient is listed there marked as "Send Regular Mail" and "Auto enrollment triggered".</p>--}}
{{--          </div>--}}
{{--      </div>--}}

{{--            <br>--}}

            <div class="request-info-href" style="padding-top: 20px;">
                <h5>Enter patient 'ID' that got enrolled</h5>
                <form action="{{route('evaluate.survey.completed')}}" target="_blank">
                    <label for="enrolleeId">Enter Id:</label><br>
                    <input type="text" id="enrolleeId" name="enrolleeId" placeholder="ex.1616"><br>
                    <input type="submit" value="Submit">
                </form>
            </div>

{{--            <div class="request-info-href">--}}
{{--                <a href="{{route('reset.test.qa')}}">--}}
{{--                    <button type="button" class="btn btn-warning">Reset Test--}}
{{--                    </button>--}}
{{--                </a>--}}
{{--            </div>--}}
        </div>

{{--    <div>--}}
{{--        <h5>Invited Patients credentials</h5>--}}
{{--        @foreach($invitationData as $data)--}}
{{--            @if($data['isEnrolleeClass'])--}}
{{--                <ol>--}}
{{--                    <strong> Type: Enrollee.</strong>--}}
{{--                    <br>--}}
{{--                    Invited Name: {{$data['name']}}--}}
{{--                    <br>--}}
{{--                    DOB: {{$data['dob']}}--}}
{{--                    <br>--}}
{{--                    <a href="{{$data['invitationUrl']}}" target="_blank">click here</a>--}}
{{--                    or the link in Sms / Email. <br>--}}
{{--                    Open in incognito window or log out from this browser.--}}
{{--                </ol>--}}

{{--            @elseif($data['isEnrolleeClass'] === false)--}}
{{--                    <ol>--}}
{{--                        <strong>Type: Unreachable Patient.</strong>--}}
{{--                        <br>--}}
{{--                        Invited Name: {{$data['name']}}--}}
{{--                        <br>--}}
{{--                        DOB: {{$data['dob']}}--}}
{{--                        <br>--}}
{{--                        You can <a href="{{$data['invitationUrl']}}" target="_blank">click here</a>--}}
{{--                        or the link in Sms / Email. <br>--}}
{{--                        Open in incognito window or log out from this browser.--}}
{{--                    </ol>--}}
{{--            @endif--}}

{{--        @endforeach--}}
{{--    </div>--}}

</div>
@endsection