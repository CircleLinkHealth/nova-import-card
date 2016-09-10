<!-- PAGE TIMER START -->
<div class="modal fade" id="timerModal" role="dialog" style="height: 10000px; opacity: 1;background-color: black">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h4 class="modal-title" id="myModalLabel">You have gone idle....</h4>
            </div>
            <div class="modal-body">
                <p style="font-size:125%;">Uh oh, we haven’t heard from you in a while<img src="{{ asset('/img/tear-face-emoji.png') }}" style="width:30px;height:25px;" />. Were you working on a specific patient while we were idle?</p>
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
$qs = '';
$option = 'att_config';
if (!isset($activity)) {
    $activity = 'Undefined';
}
$role = '';
$title = Route::currentRouteName(); //get_the_title();


// ip address stuff
if (!function_exists('get_ip_address')) {
    function get_ip_address()
    {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }
}


/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
if (!function_exists('validate_ip')) {

    function validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }
}


$ipAddr = get_ip_address();
if (!$ipAddr) {
    $ipAddr = '';
}


// url stuff
$pieces = explode("?", $_SERVER['REQUEST_URI']);
$urlShort = $pieces[0];

// should we process time on this page?
$enableTimeTracking = true;
//$enableTimeTracking = false; // override it

// disable if login
if (strpos($_SERVER['REQUEST_URI'], 'login') !== false) {
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

        var startTime = new Date();
        var noResponse = true; // set to false if user clicks yes/no button
        var totalTime = 0; // total accumulated time on page
        var modalDelay = 60000 * 8; // ms modal waits before force logout (60000 = 1min)
        var isTimerProcessed = false;
        var redirectLocation = false;
        var idleTime = 120000; // ms before modal display (60000 = 1min)
        var consoleDebug = true; // debug toggle

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
            if (consoleDebug) console.log('totalTime after adding '+(endTime - startTime)+' = ' + totalTime);

            // reset startTime to time modal was opened
            startTime = new Date();
            if (consoleDebug) console.log('set startTime to 0');

            function millisToMinutesAndSeconds(millis) {
                var minutes = Math.floor(millis / 60000);
                var seconds = ((millis % 60000) / 1000).toFixed(0);
                return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
            }

            // set html to modal and instantiate
            //$('#timeModalTotal').html(' Total elapsed: ' + millisToMinutesAndSeconds(totalTime) + ' minutes');
            if (consoleDebug) console.log('display timerModal()');
            $('#timerModal').modal('show');

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
                if (consoleDebug) console.log('totalTime = ' + totalTime);
                // subtract 45 seconds for modal idle = 45000
                // subtract 9:30 for modal idle = 1000*60*modalDelay - 90000
                //totalTime = ( totalTime - modalDelay - 90000 );
                redirectLocation = 'logout';
                submitTotalTime();
            }

            // yes/no button in modal
            $('#timeModalYes').on("click", function () {
                if (consoleDebug) console.log('yes clicked, setting document idleTimer over again, idleTime = '+idleTime);
                $(document).idleTimer(idleTime);
                return true;
            });

            // yes/no button in modal
            $('#timeModalNo').on("click", function () {
                //alert('not reviewing patient anymore, complete and submit ' + totalTime + 'seconds');
                $("#timerDebug").html("no longer reviewing... totalTime = " + totalTime + "");
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
                //startTime = new Date(); <-- we dont want to restart timer here
                $(document).idleTimer("resume");
                $('#timeModalNo, #timeModalYes').unbind('click');
                // deactivate noResponseTimer
                clearTimeout(noResponseTimer);
                return true;
            });

        });

        // this runs when the browser window is closed
        window.onbeforeunload = function () {
            //alert('Elapsed: ');
            $(document).idleTimer("pause");
            endTime = new Date();
            totalTime = (totalTime + (endTime - startTime));
            submitTotalTime();
            //return false;
        };

        // this is the ajax call that is made to store the time
        // first store time than redirect based on result, logout if idle
        function submitTotalTime() {
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
                "startTime": '<?php echo date('Y-m-d H:i:s'); ?>',
                "urlFull": '<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>',
                "urlShort": '<?php echo $urlShort; ?>',
                "ipAddr": '<?php echo $ipAddr; ?>',
                "activity": $('#activityName').val(),
                "title": '<?php echo $title; ?>',
                "qs": '<?php echo $qs; ?>'
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
<span id="timerDebug" style="display:none;">
    Page Route Name: {{ Route::currentRouteName() }}<br>
    Tracking Enabled: <?php echo $enableTimeTracking; ?>
</span>
<!-- PAGE TIMER END -->

