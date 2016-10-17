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

$qs = '';
$option = 'att_config';
if (!isset($activity)) {
    $activity = 'Undefined';
}
$role = '';
$title = Route::currentRouteName(); //get_the_title();

$ipAddr = Request::ip();

$requestUri = Request::getRequestUri();
// url stuff
$pieces = explode("?", $requestUri);
$urlShort = $pieces[0];

// should we process time on this page?
$enableTimeTracking = !isset($disableTimeTracking);
//$enableTimeTracking = false; // override it

// disable if login
if (strpos($requestUri, 'login') !== false) {
    $enableTimeTracking = false;
}

// set patient vars
$patientId = '';
$patientProgramId = '';
if (isset($patient) && !empty($patient)) {
    //$enableTimeTracking = false;
    $patientId = $patient->ID;
    $patientProgramId = $patient->program_id;
}
if ($enableTimeTracking) {
?>
<script>
    (function ($) {
        var startTime = new Date('<?php echo Carbon::now()->format('D M d Y H:i:s O'); ?>');
        var noResponse = true; // set to false if user clicks yes/no button
        var totalTime = 0; // total accumulated time on page
        var modalDelay = 60000 * 8; // ms modal waits before force logout (60000 = 1min)
        var isTimerProcessed = false;
        var redirectLocation = false;
        var idleTime = 60000 * 2; // ms before modal display (60000 = 1min)
        var consoleDebug = false;


        if (consoleDebug) console.log('start time: ' + startTime);

        // instantiate idleTimer
        if (consoleDebug) console.log('setting idleTimer @ ' + idleTime);
        $(document).idleTimer(idleTime);

        // idleTimer ^
        $(document).on("idle.idleTimer", function (event, elem, obj) {
            if (consoleDebug) console.log('idleTimer hit');
            //if (consoleDebug) console.log('totalTime = ' + totalTime);
            // set to false if user clicks yes/no button
            noResponse = true;

            // pause timer
            $(document).idleTimer("pause");
            if (consoleDebug) console.log('paused idleTimer');
            if (consoleDebug) console.log('totalTime before calc = ' + totalTime);

            // we went idle, add previously active time to total time
            endTime = new Date();
            totalTime = (totalTime + (endTime - startTime));

            // remove 90000 of the 120000 seconds here
            //totalTime = (totalTime - 90000);
            //if (consoleDebug) console.log('added previously active time to total time than removed 90000 (only 30 seconds of the 2 minutes idle counts)');
            if (consoleDebug) console.log('totalTime after adding ' + (endTime - startTime) + ' = ' + totalTime);

            // reset startTime to time modal was opened
//            startTime = new Date();
            if (consoleDebug) console.log('set startTime to 0');

            function millisToMinutesAndSeconds(millis) {
                var minutes = Math.floor(millis / 60000);
                var seconds = ((millis % 60000) / 1000).toFixed(0);
                return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
            }

            // set html to modal and instantiate
            //$('#timeModalTotal').html(' Total elapsed: ' + millisToMinutesAndSeconds(totalTime) + ' minutes');
            if (consoleDebug) console.log('display timerModal()');
            $('#timerModal').modal({backdrop: 'static', keyboard: false});

            // no response logic
            // http://www.sitepoint.com/jquery-settimeout-function-examples/

            // if no response to modal, log out after {modalDelay}
            if (consoleDebug) console.log('modalDelay = ' + modalDelay + ' time modal will wait to force logout ');
            var noResponseTimer = setTimeout(noResponseTotalTime, modalDelay);

            function noResponseTotalTime() {
                if (consoleDebug) console.log('noResponseTotalTime() start');
                if (consoleDebug) console.log('totalTime = ' + totalTime);
                // we went idle, add previously active time to total time
                //endTime = new Date();
                //totalTime = (totalTime + (endTime - startTime)) - modalDelay;
                //totalTime = (totalTime - modalDelay);

                // subtract 45 seconds for modal idle = 45000
                // subtract 9:30 for modal idle = 1000*60*modalDelay - 90000
                //totalTime = ( totalTime - modalDelay - 90000 );

                //remove 90000 of the 120000 seconds here
                if (consoleDebug) console.log('remove 90000 of the initial 120000 second idle period here');
                totalTime = (totalTime - 90000);
                if (consoleDebug) console.log('totalTime = ' + totalTime);
                redirectLocation = 'logout';
                submitTotalTime();
            }

            // yes/no button in modal
            $('#timeModalYes').on("click", function () {
                if (consoleDebug) console.log('yes clicked, doing nothing here, idleTime = ' + idleTime);
                return true;
            });

            // yes/no button in modal
            $('#timeModalNo').on("click", function () {
                //alert('not reviewing patient anymore, complete and submit ' + totalTime + 'seconds');
                $("#timerDebug").html("no longer reviewing... totalTime = " + totalTime + "");
                if (consoleDebug) console.log('remove 90000 of the initial 120000 second idle period here');
                totalTime = (totalTime - 90000);
                if (consoleDebug) console.log('totalTime = ' + totalTime);
                $('#timeModalNo, #timeModalYes').unbind('click');
                // deactivate noResponseTimer
                clearTimeout(noResponseTimer);
                redirectLocation = 'home';
                submitTotalTime();
                return true;
            });

            // if modal is closed (from clicking outside the modal in the grey area), force cancellation of modal idle timer
            $('#timerModal').on('hide.bs.modal', function (e) {
                $("#timerDebug").html("still reviewing{hidden}... totalTime = " + totalTime + "");
                if (consoleDebug) console.log('running hide.bs code here');
                //startTime = new Date(); <-- we dont want to restart timer here
                $(document).idleTimer("resume");
                $('#timeModalNo, #timeModalYes').unbind('click');
                // deactivate noResponseTimer
                clearTimeout(noResponseTimer);
                return true;
            });

        });

        // this runs when the browser window is closed
        //no it doesn't. It's when content unloads
        window.onbeforeunload = function () {
            $(document).idleTimer("pause");
            endTime = new Date();
            totalTime = (endTime - startTime);
            submitTotalTime(true);
        };

        // this is the ajax call that is made to store the time
        // first store time than redirect based on result, logout if idle
        function submitTotalTime(deletePatientSession) {

            if (deletePatientSession === undefined) {
                deletePatientSession = false;
            }

            if (consoleDebug) console.log('start submitTotalTime()');
            if (consoleDebug) console.log('totalTime = ' + totalTime);
            if (isTimerProcessed == true) {
                return true;
            }
            $('#timerModal').modal('hide');
            $("#timerDebug").html("COMPLETED, totalTime = " + totalTime + " .. LEAVE PAGE -> LOGOUT");
            if (consoleDebug) console.log("COMPLETED, totalTime = " + totalTime + " .. LEAVE PAGE -> LOGOUT");
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
                "qs": '<?php echo $qs; ?>',
                "deletePatientSession": deletePatientSession
            };

            if (consoleDebug) console.log(data);

            $.ajax({
                type: "POST",
                url: '<?php echo URL::route('api.pagetracking'); ?>',
                data: data,
                //cache: false,
                encode: true,
                //processData: false,
                success: function (data) {
                    // redirect
                    if (redirectLocation) {
                        if (redirectLocation == 'logout') {
                            window.location.href = "<?php echo url('/auth/logout'); ?>";
                        } else if (redirectLocation == 'home') {
                            window.location.href = "<?php echo URL::route('patients.dashboard'); ?>";
                        }
                    }
                }
            });

            // set timer as inactive since already processed
            isTimerProcessed = true;

            return false;
        }

        //submitTotalTime();
    })(jQuery);
</script>
<?php
} // end enableTimeTracking check
?>


<!--<h3>TIMER DEBUG</h3>-->
{{--<span id="timerDebug" style="display:none;">--}}
{{--Page Route Name: {{ Route::currentRouteName() }}<br>--}}
{{--Tracking Enabled: {{ $enableTimeTracking }}--}}
{{--</span>--}}
{{--<!-- PAGE TIMER END -->--}}

