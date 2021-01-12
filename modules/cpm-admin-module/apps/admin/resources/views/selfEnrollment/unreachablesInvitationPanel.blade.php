{{--@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')--}}
{{--@section('title', 'Enrollment Test Panel')--}}
{{--@section('activity', 'Enrollment Test Panel')--}}
{{--@section('content')--}}
<div class="container">
    <div class="content">
        @if(session()->has('message'))
            <div class="alert alert-info">
                {{session()->get('message')}}
            </div>
        @endif

        <h4 style="text-align: center">Test Dashboard (styling temporarily disabled)</h4>
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
                       <option value="marillac-clinic-inc">Marillac Health</option>
                       <option value="cameron-memorial">Cameron Memorial</option>
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
            <div class="request-info-href" style="padding-top: 20px;">
                <h5>Enter <strong>USER</strong> 'ID' that got enrolled(NOT the enrollee_id)</h5>
                <form action="{{route('evaluate.survey.completed')}}" target="_blank">
                    <label for="enrolleeId">Enter Id:</label><br>
                    <input type="text" id="enrolleeId" name="enrolleeId" placeholder="ex.1616"><br>
                    <input type="submit" value="Submit">
                </form>
            </div>

        </div>


{{--</div>--}}
{{--@endsection--}}