<!-- PAGE TIMER START -->
<?php

// patient stuff
$patientId = 0;
if ( !empty(empty($patient)) ) {
    $patientId = $patient->ID;
}

$qs = '';
$option = 'att_config';
$activity = '';
$role = '';
$title  = 'Patient Overview Review'; //get_the_title();
//$activity_assignment = get_option($option,'No Data');
$activity_assignment = '{"Patient Summary":{"user=%":{"activity":"Patient Overview Review"},"detail=obs_biometrics":{"activity":"Biometrics Data Review"},"detail=obs_lifestyle":{"activity":"Lifestyle Data Review"},"detail=obs_medications":{"activity":"Medications Data Review"},"detail=obs_symptoms":{"activity":"Symptoms Data Review"}},"Add Patient":{"user=%":{"activity":"Edit/Modify Care Plan"},"":{"activity":"Initial Care Plan Setup"}},"Patient Care Plan":{"user=%":{"activity":"Edit/Modify Care Plan"},"np=1":{"activity":"Initial Care Plan Setup"}},"Patient Care Plan II":{"user=%":{"activity":"Edit/Modify Care Plan"},"np=1":{"activity":"Initial Care Plan Setup"}},"Patient Additional Information":{"user=%":{"activity":"Edit/Modify Care Plan"},"np=1":{"activity":"Initial Care Plan Setup"}},"Input Observations":{"user=%":{"activity":"Input Observations"}},"Record New Activity":{"user=%":{"activity":"Input Activity"}},"Print Care Plan":{"user=%":{"activity":"Care Plan View/Print"},"np=1":{"activity":"Initial Care Plan Setup"}},"Alerts":{"":{"activity":"Alerts Review"},"user=%":{"activity":"Patient Alerts Review"}},"Progress Report":{"user=%":{"activity":"Progress Report Review/Print"}},"Patient Activity Report":{"user=%":{"activity":"Patient Activity Report Review"}}}';
// var_dump($activity_assignment);
if(!is_array($activity_assignment)) {
    $activity_assignment = json_decode($activity_assignment, 1);
}
/*
if ($_REQUEST['user']) $qs = 'user=%';
if ($_REQUEST['detail']) $qs = 'detail='.$_REQUEST['detail'];
if ($_REQUEST['np']) $qs = 'np='.$_REQUEST['np'];
*/
if (! Auth::guest()) {
    /*
    $meta = get_user_meta(Auth::user()->ID, 'wp_' . $blogId . '_capabilities', true);
    //var_dump($meta);
    $activity = $activity_assignment[$title][$qs]['activity'];
    $role = array_keys($meta);
    if($_GET['d'])	echo "Activity: <strong>$activity</strong> for page $title using: $qs. Your Role is: " . $role[0];
    */
}

// ip address stuff
function get_ip_address() {
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


/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return false;
    }
    return true;
}

$ipAddr = get_ip_address();
if(!$ipAddr) {
    $ipAddr = '';
}

// url stuff
$pieces = explode("?", $_SERVER['REQUEST_URI']);
$urlShort = $pieces[0];


// should we process time on this page?
$enableTimeTracking = true;
$enableTimeTracking = false; // override it
if (strpos($_SERVER['REQUEST_URI'],'login') !== false) {
    $enableTimeTracking = false;
}
if($enableTimeTracking) {
?>
<script>
    (function ($) {

        var startTime = new Date();
        var noResponse = true; // set to false if user clicks yes/no button
        var totalTime = 0;
        var isTimerProcessed = false;
        var redirectLocation = false;
        $(document).on("idle.idleTimer", function (event, elem, obj) {
            $("#timerDebug").html("time limit hit.....");
            // set to false if user clicks yes/no button
            noResponse = true;

            // pause timer
            $(document).idleTimer("pause");

            // debug msg
            $("#timerDebug").html("idle, loading modal");

            // we went idle, add previously active time to total time
            endTime = new Date();
            totalTime = (totalTime + (endTime - startTime));

            // reset startTime to time modal was opened
            startTime = new Date();

            function millisToMinutesAndSeconds(millis) {
                var minutes = Math.floor(millis / 60000);
                var seconds = ((millis % 60000) / 1000).toFixed(0);
                return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
            }

            // set html to modal and instantiate
            //$('#timeModalTotal').html(' Total elapsed: ' + millisToMinutesAndSeconds(totalTime) + ' minutes');
            $('#timerModal').modal();

            // no response logic
            // http://www.sitepoint.com/jquery-settimeout-function-examples/
            var delayMin = 8;
            function noResponseTotalTime() {
                // we went idle, add previously active time to total time
                endTime = new Date();
                totalTime = (totalTime + (endTime - startTime));
                // subtract 45 seconds for modal idle = 45000
                // subtract 9:30 for modal idle = 1000*60*delayMin - 90000

                totalTime = ( totalTime - 1000*60*delayMin - 90000 );
                redirectLocation = 'logout';
                submitTotalTime();
            }

            // if no response to modal, log out after {delayMin}
            var noResponseTimer = setTimeout(noResponseTotalTime, 1000*60*delayMin);

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
                startTime = new Date();
                $(document).idleTimer("resume");
                $('#timeModalNo, #timeModalYes').unbind('click');
                // deactivate noResponseTimer
                clearTimeout(noResponseTimer);
                return true;
            });

        });

        window.onbeforeunload = function () {
            //alert('Elapsed: ' + $( document ).idleTimer("getElapsedTime"));
            $(document).idleTimer("pause");
            endTime = new Date();
            totalTime = (totalTime + (endTime - startTime));
            submitTotalTime();
            //return false;
        };

        // instantiate ifleTimer @ 2 min
        $(document).idleTimer(12000);

        function submitTotalTime() {
            if (isTimerProcessed == true) {
                return true;
            }
            $('#timerModal').modal('hide');
            $("#timerDebug").html("COMPLETED, totalTime = " + totalTime + " .. LEAVE PAGE -> LOGOUT");
            $(document).idleTimer("pause");


            var data = {
                "patientId": '<?php echo $patientId; ?>',
                "providerId": '<?php Auth::user()->ID ?>',
                "totalTime": totalTime,
                "programId": '<?php echo $patient->program_id; ?>',
                "startTime": '<?php echo date('Y-m-d H:i:s'); ?>',
                "urlFull": '<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>',
                "urlShort": '<?php echo $urlShort; ?>',
                "ipAddr": '<?php echo $ipAddr; ?>',
                "activity": '<?php echo $activity; ?>',
                "title": '<?php echo $title; ?>',
                "qs": '<?php echo $qs; ?>'
            };

            $.ajax({
                type: "POST",
                url: 'api/v2.1/pagetimer',
                data: data,
                //cache: false,
                encode: true,
                //processData: false,
                success: function (data) {
                    // redirect
                    if (redirectLocation) {
                        if (redirectLocation == 'logout') {
                            alert('logout');
                            window.location.href = "<?php echo 'site_url()?ajaxlogout=1'; ?>";
                        } else if (redirectLocation == 'home') {
                            alert('home');
                            window.location.href = "<?php echo 'site_url()'; ?>";
                        }
                    }
                }
            });

            // set timer as inactive since already processed
            isTimerProcessed = true;

            return false;
        }

    })(jQuery);
</script>
<?php
} // end enableTimeTracking check
?>
<div class="modal fade" id="timerModal" tabindex="-1" role="dialog" aria-labelledby="timerModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                You have gone idle....
            </div>
            <div class="modal-body">
                <p>We haven’t heard from you in a while. Do you wish to keep this session open?</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="timeModalNo" class="btn btn-warning" data-dismiss="modal">No</button>
                <button type="button" id="timeModalYes" class="btn btn-success"  data-dismiss="modal">Yes</button>
            </div>
        </div>
    </div>
</div>


<h3>TIMER DEBUG</h3>
<span id="timerDebug"><?php echo $enableTimeTracking; ?></span>
<!-- PAGE TIMER END -->