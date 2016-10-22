<!-- PAGE TIMER START -->
<div class="modal fade" id="timerModal" role="dialog" style="height: 10000px; opacity: 1;background-color: black">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>-->
                <h4 class="modal-title" id="myModalLabel">You have gone idle....</h4>
            </div>
            <div class="modal-body">
                <p style="font-size:125%;">We haven’t heard from you in a while <img
                            src="{{ asset('/img/emoji-disappointed-but-relieved.png') }}"
                            style="width:25px; height:25px; margin-bottom:5px;"/>. Were you working on a specific
                    patient while we were idle?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="timeModalNo" class="btn btn-warning" data-dismiss="modal">No</button>
                <button type="button" id="timeModalYes" class="btn btn-success" data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="activityName" id="activityName" value="@yield('activity')">

<?php
use Carbon\Carbon;

if (!isset($activity)) {
    $activity = 'Undefined';
}

$title = Route::currentRouteName();

$ipAddr = Request::ip();

$requestUri = Request::getRequestUri();
$pieces = explode("?", $requestUri);
$urlShort = $pieces[0];

$enableTimeTracking = !isset($disableTimeTracking);

// disable if login
if (strpos($requestUri, 'login') !== false) {
    $enableTimeTracking = false;
}

// set patient vars
$patientId = '';
$patientProgramId = '';
if (isset($patient) && !empty($patient)) {
    $patientId = $patient->ID;
    $patientProgramId = $patient->program_id;
}
?>

@if ($enableTimeTracking)
    <script>
        (function ($) {
            //We get startTime, and endTime from the client to not have to deal with timezones
            var startTime = new Date();
            var endTime;
            var noResponse = true; // set to false if user clicks yes/no button
            var totalTime = 0; // total accumulated time on page
            var modalDelay = 60000 * 8; // ms modal waits before force logout (60000 = 1min)
            var isTimerProcessed = false;
            var redirectLocation = false;
            var idleTime = 60000 * 2; // ms before modal display (60000 = 1min)

            //start idle timer
            $(document).idleTimer(idleTime);

            //once we go idle
            $(document).on("idle.idleTimer", function (event, elem, obj) {
                // set to false if user clicks yes/no button
                noResponse = true;

                $(document).idleTimer("pause");

                // we went idle, add previously active time to total time
                endTime = new Date();
                totalTime = (totalTime + (endTime - startTime));

                // reset startTime to time modal was opened
                startTime = new Date();

                $('#timerModal').modal({backdrop: 'static', keyboard: false});

                // if no response to modal, log out after {modalDelay}
                var noResponseTimer = setTimeout(function () {
                    totalTime = (totalTime - 90000);
                    redirectLocation = 'logout';
                    submitTotalTime(true);
                }, modalDelay);


                $('#timeModalYes').on("click", function () {
                    return true;
                });

                $('#timeModalNo').on("click", function () {
                    totalTime = (totalTime - 90000);

                    $('#timeModalNo, #timeModalYes').unbind('click');
                    clearTimeout(noResponseTimer);
                    redirectLocation = 'home';
                    submitTotalTime(true);
                    return true;
                });

                // if modal is closed (from clicking outside the modal in the grey area), force cancellation of modal idle timer
                $('#timerModal').on('hide.bs.modal', function (e) {
                    $(document).idleTimer("resume");
                    $('#timeModalNo, #timeModalYes').unbind('click');

                    clearTimeout(noResponseTimer);
                    return true;
                });

            });

            window.onbeforeunload = function () {
                $(document).idleTimer("pause");
                endTime = new Date();
                totalTime = (endTime - startTime);
                submitTotalTime(true);
            };

            function submitTotalTime(deletePatientSession) {

                if (deletePatientSession === undefined) {
                    deletePatientSession = false;
                }

                if (isTimerProcessed == true) {
                    return true;
                }
                $('#timerModal').modal('hide');

                $(document).idleTimer("pause");


                var data = {
                    "patientId": '<?php echo $patientId; ?>',
                    "providerId": '<?php echo Auth::user()->ID ?>',
                    "totalTime": totalTime,
                    "programId": '<?php echo $patientProgramId; ?>',
                    "startTime": '<?php echo Carbon::now()->toDateTimeString(); ?>',
                    "urlFull": '<?php echo Request::url(); ?>',
                    "urlShort": '<?php echo $urlShort; ?>',
                    "ipAddr": '<?php echo $ipAddr; ?>',
                    "activity": $('#activityName').val(),
                    "title": '<?php echo $title; ?>',
                    "deletePatientSession": deletePatientSession,
                    "redirectLocation": redirectLocation
                };

                $.ajax({
                    type: "POST",
                    url: '<?php echo URL::route('api.pagetracking'); ?>',
                    data: data,
                    encode: true,
                    success: function (data) {
                        if (redirectLocation) {
                            if (redirectLocation == 'logout') {
                                window.location.href = "<?php echo url('/auth/logout'); ?>";
                            } else if (redirectLocation == 'home') {
                                window.location.href = "<?php echo URL::route('patients.dashboard'); ?>";
                            }
                        }
                    }
                });

                isTimerProcessed = true;

                return false;
            }

        })(jQuery);
    </script>

@endif