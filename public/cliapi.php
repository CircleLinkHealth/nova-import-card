<!DOCTYPE html>
<html lang="en">
<head>
	<title></title>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,700">
	<link rel="stylesheet"  href="//getbootstrap.com/dist/css/bootstrap.min.css"/>  
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet"  href="//demos.jquerymobile.com/1.4.5/css/themes/default/jquery.mobile-1.4.5.min.css"/>  
	<link rel="stylesheet" href="//demos.jquerymobile.com/1.4.5/_assets/css/jqm-demos.css"/>

	<script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/_assets/js/index.js"></script>
 	<script type='text/javascript' src='//code.jquery.com/jquery-2.1.4.js'></script>
	<script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/js/jquery.mobile-1.4.5.min.js"></script>
	 <script type='text/javascript' src="//demos.jquerymobile.com/1.4.5/js/jquery.js"></script>
 <style id="full-width-slider">
        /* Hide the number input */
        .full-width-slider input {
            display: none;
        }
        .full-width-slider .ui-slider-track {
            margin-left: 15px;
        }
    </style>
	<script id="dynamic-slider">
$( document ).on( "pagecreate", function() {
	$( "<input type='number' data-type='range' min='0' max='100' step='1' value='17'>" )
		.appendTo( "#dynamic-slider-form" )
		.slider()
		.textinput()
});
	</script>

	<style type="text/css">
		@-webkit-keyframes fade-out {
		0% { opacity: 1; }
		100% { opacity: 0;}
		}

		#alert-msg {
		    display:inline-block;
		 float:left;
		 border:1px solid #060;
		 background:#FFC;
		 padding:10px 20px;
		 box-shadow:2px 2px 4px #666;
		 color:Navy;
		 font-weight:bold;
		 /*display:none;*/
		}


		#alert-msgS {
		    -webkit-animation: fade-out 10s ease-in;
		    -webkit-animation-fill-mode: forwards;
		    -webkit-animation-iteration-count: 1;
		}
	</style>
	</head>
<body>
<?php 

	// if (!isset($_COOKIE['resu'])) {
	// 	$user = "gaurav@oabstudios.com";
	// 	$pass = "v&gHo1O!Q&ybXWX#miIhvWj1";

	// 	setcookie("resu", $user, time()+36000);  /* expire in 1 hour */
	// 	setcookie("ssap", $pass, time()+36000);  /* expire in 1 hour */
	// 	print_r($_COOKIE);
	// }

// Print an individual cookie
// echo "<pre>";
// echo "</pre>";

// Another way to debug/test is to view all cookies
?>
<div class="container">
<h6>Attempting Login...

<?php
/*
*
*	POST to Login API with given credentials to receive a login TOKEN
*
*	Params:
*	"user_email: gaurav@oabstudios.com",
*	"user_pass: v&gHo1O!Q&ybXWX#miIhvWj1"
*	"x-authorization: 0252f5feac4a511da272a7f0db07b3e1a1a3dc2d"
*
*/

$u = "yate@circlelinkhealth.com";
$p = "yate";

$u = "gaurav@oabstudios.com";
$p = "v&gHo1O!Q&ybXWX#miIhvWj1";

 $u = $_COOKIE['resu'];
 $p = $_COOKIE['ssap'];

$curlOpt_url = "https://api-test.careplanmanager.com/api/v2.1/";
// $curlOpt_url = "http://clapi.cpm.com/api/v2.1/";
$x_auth = "0252f5feac4a511da272a7f0db07b3e1a1a3dc2d";
// $x_auth = "740be88cebe7b6e09267212ae5ee099ab8506170";

if($_POST) { 
	// var_dump($_POST);
	$valid = false;
if ($_POST['SUBMIT'] == 'OBS') {
echo "<div id='alert-msg'>";
	switch ($_POST['ReturnFieldType'])  {
		case 'Range':
			if (!is_numeric($_POST['obs_val'])) break;
			if ($_POST['obs_val'] >= $_POST['ReturnDataRangeLow'] && 
				$_POST['obs_val'] <= $_POST['ReturnDataRangeHigh']) {
				echo "Valid Answer<BR>"; 
				$valid = true;
			} else {
				echo "Not a Valid Answer: Range is from " . $_POST['ReturnDataRangeLow'] . " to " . $_POST['ReturnDataRangeHigh'];
			
			}
			break;
		case 'menu':
		case 'List':
			if (strpos('x'.$_POST['ReturnValidAnswers'], strtoupper($_POST['obs_val'])) > 0) {
				echo "Valid Answer<BR>";
				$valid = true;
			} else {
				echo "Not a Valid Answer " . strtoupper($_POST['obs_val']) . " != " . strtoupper($_POST['ReturnValidAnswers']);
			}
			break;
		default:
			# code...
			break;
	}
// echo $_POST['Obs_Key'] . $_POST['obs_val'] . $_POST['MessageID'] . $_POST['Obs_Date'] . $arrToken;
	if($valid) {
			$arrToken = getToken($u, $p, $curlOpt_url, $x_auth);
			postObservation($curlOpt_url, $x_auth, $_POST['Obs_Key'] , strtoupper($_POST['obs_val']) , $_POST['MessageID'] , $_POST['Obs_Date'], $arrToken);
		}

echo "</div>";
?>
<script>
          $("#alert-msg").show().delay(5000).fadeOut();
</script>
<?php
	// exit();
}

	if ($_POST['SUBMIT'] == 'ID') {
			if (isset($_POST['u'])) {
			$u = $user = $_POST['u'];
			$p = $pass = $_POST['p'];

			setcookie("resu", $user, time()+36000);  /* expire in 1 hour */
			setcookie("ssap", $pass, time()+36000);  /* expire in 1 hour */
	// print_r($_COOKIE);
		}

		// var_dump($_POST);
	}
}

	$arrToken = getToken($u, $p, $curlOpt_url, $x_auth);
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$curlOpt_url"."careplan",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "authorization: Bearer " . $arrToken['token'],
	    "client: mobi",
	    "content-type: multipart/form-data; boundary=---011000010111000001101001",
	    "x-authorization: $x_auth"
	  ),
	));
	$jsonCarePlan = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err || $jsonCarePlan == 'Unauthorized.') {
		echo "<BR>cURL Error #:" . $err . $jsonCarePlan;
	var_dump("authorization: Bearer " . $arrToken['token']);
	var_dump("x-authorization: $x_auth");
		die("<span class='btn-danger'>Error Connecting to CarePlan Feed API</span></h6>");
	} else {
	// echo $jsonCarePlan;
		$arrCarePlan = json_decode($jsonCarePlan, true);
		echo "<span class='btn-success'>CarePlan Feed Retrieval Sucessful.</span></h6>";

	}

?>
<div class="row col-lg-10 col-lg-offset-2" data-role="collapsible" data-theme="b">
	<h2>Set User</h2>
	<form action='' method='post'>
		<input class='form-control' id="u" name="u" value="<?= $u ?>">
		<input class='form-control' id="p" name="p" type="password" value="<?= $p ?>">
		<input class='form-control' id="SUBMIT" name="SUBMIT" type="hidden" value="ID">
		<button data-theme='b' class='btn btn-primary col-sm-1' type='submit'>Set</button>
	</form>
</div>
<DIV class="row" data-role="collapsible-set" >
	<h1>CarePlan Feed</h1>
</DIV>
<?php 


foreach ($arrCarePlan['CP_Feed'] as $key => $value) {
?><div class="row col-lg-12" data-role="collapsible" data-theme="b">
		<h2><?php	
	echo $arrCarePlan['CP_Feed'][$key]['Feed']['FeedDate']
?></h2> <?php	

	$arrSectionOrder = array();
	$arrSectionOrder = array('Messages','Biometric','DMS','Symptoms','Reminders');

	foreach ($arrSectionOrder as $section) {
		if($section == 'Symptoms') {
			echo '<div class="row col-lg-10 col-lg-offset-2" data-role="collapsible" data-theme="b">';
			echo "<h3>Would you like to report any Symptoms?</h3>";
		}
		foreach($arrCarePlan['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio){
			// var_dump($arrBio['ReturnFieldType']);
			echo "<form action='' method=post>\n";
			echo "<div class='row'>\n";
			switch ($arrBio['MessageIcon']) {
				case 'bp';
				case 'bs';
					$msgIcon = 'clock-o';
					break;
				case 'wt':
					$msgIcon = 'balance-scale';
					break;
				case 'cs':
					$msgIcon = 'ban';
					break;
				case 'call':
					$msgIcon = 'phone';
					break;
				case 'hsp':
					$msgIcon = 'hospital-o';
					break;
				case 'reminder':
					$msgIcon = 'sticky-note-o';
					break;
				case 'info':
				case 'tip':
					$msgIcon = 'info-circle';
					break;
				
				default:
					$msgIcon = $arrBio['MessageIcon'];
					break;
			}
			echo "<div class='col-sm-1'><i style='color:Blue' class='fa fa-2x fa-". $msgIcon ."'></i></div>\n";
			echo "<div class='col-sm-4'>" . $arrBio['MessageContent'] . "</div>\n";
			// echo " [" . $arrBio['MessageID'] . " | `" . $arrBio['Obs_Key'] . "` | " . date('Y-m-d H:i:s O)') . "] <BR>";
			if ($arrBio['ReturnFieldType'] == 'None' || $arrBio['PatientAnswer'] ) {
				if ($arrBio['PatientAnswer'] )	echo "<div class='col-sm-6'>You Answered: " . $arrBio['PatientAnswer'] . " @ <small>". date('h:i:s A',$arrBio['ResponseDate']) . "</small></div>\n";
			} else {
				echo "\n<input class='form-control' type='hidden' name='SUBMIT' value='OBS'>";
				echo "\n<input class='form-control' type='hidden' name='Obs_Key' value='". $arrBio['Obs_Key'] ."'>";
				echo "\n<input class='form-control' type='hidden' name='MessageID' value='". $arrBio['MessageID'] ."'>";
				echo "\n<input class='form-control' type='hidden' name='Obs_Date' value='". date("Y-m-d H:i:s") ."'>";
				echo "\n<input class='form-control' type='hidden' name='ReturnFieldType' value='". $arrBio['ReturnFieldType'] ."'>";
				echo "\n<input class='form-control' type='hidden' name='ReturnDataRangeLow' value='". $arrBio['ReturnDataRangeLow'] ."'>";
				echo "\n<input class='form-control' type='hidden' name='ReturnDataRangeHigh' value='". $arrBio['ReturnDataRangeHigh'] ."'>";
				echo "\n<input class='form-control' type='hidden' name='ReturnValidAnswers' value='". $arrBio['ReturnValidAnswers'] ."'>";
				echo "";
				echo "\n<div class='col-sm-4'>";
				$type = null;
				switch ($arrBio['ReturnFieldType']) {
					case 'Range':
						$type = "type='range' data-type='". $arrBio['ReturnFieldType']. "' min='" .$arrBio['ReturnDataRangeLow']. "' max='" .$arrBio['ReturnDataRangeHigh']. "' value='0' data-theme='b' data-track-theme='c'";
							echo "\n<input $type id='obs_val' name='obs_val' value='".$arrBio['PatientAnswer']."' REQUIRED>";
						break;
					case 'List':
						echo "\n".'<select name="obs_val" id="obs_val" data-role="slider">
	<option value="N">No</option>
	<option value="Y">Yes</option>
</select>';
						break;
					default;
							echo "<input $type class='form-control col-sm-1' id='obs_val' name='obs_val' value='".$arrBio['PatientAnswer']."' REQUIRED>";

				}
				echo "<div class='ui-block-a'><button data-theme='b' class='btn btn-primary col-sm-1' type='submit'>SEND</button></div><br>\n";
				echo "</div>";
				}
			echo "</div>\n";
			echo "</form><hr>\n";
		}
		if($section == 'Symptoms') { echo "</div><hr>\n";	}
	}
?></div> <?php	
}



// var_export($arrCarePlan);
// var_dump($arrCarePlan['CP_Feed'][key($arrCarePlan['CP_Feed'])][$_REQUEST['cat']]);
// var_dump(array_keys($arrCarePlan['CP_Feed'][key($arrCarePlan['CP_Feed'])]));
// var_dump(array_keys($arrCarePlan['CP_Feed'][key($arrCarePlan['CP_Feed'])]['Biometric']));
// var_dump($arrCarePlan['CP_Feed'][key($arrCarePlan['CP_Feed'])]['Biometric']['2']); 
?>
<?php

function getToken($u=null, $p=null, $curlOpt_url, $x_auth) {
/*
*
*	GET CarePlan Feed from CarePlan API with new login TOKEN
*
*	Params:
*	"authorization: Bearer " . $arrToken['token'],
*   "client: mobi",
*	"x-authorization: 0252f5feac4a511da272a7f0db07b3e1a1a3dc2d"
*
*/


		$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "$curlOpt_url"."login",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"user_email\"\r\n\r\n$u\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"user_pass\"\r\n\r\n$p\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"X-Authorization\"\r\n\r\n$x_auth\r\n-----011000010111000001101001--",
	  CURLOPT_HTTPHEADER => array(
	    "content-type: multipart/form-data; boundary=---011000010111000001101001"
	  ),
	));
	$jsonToken = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);
	if ($err && !$jsonToken) {
	  echo "Token cURL Error #:" . $err;
		die("<BR><BR>Error Connecting to Login API<BR>$x_auth<BR>");
	} else {
		$arrToken = json_decode($jsonToken, true);
// var_export($arrToken);
		return $arrToken;
	}

}

function postObservation($curlOpt_url, $x_auth, $obs_key, $obs_value, $message_id, $obs_date, $arrToken) {
	$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "$curlOpt_url"."observation",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"obs_key\"\r\n\r\n$obs_key\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"obs_value\"\r\n\r\n$obs_value\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"obs_message_id\"\r\n\r\n$message_id\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"obs_date\"\r\n\r\n$obs_date\r\n-----011000010111000001101001--",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer ". $arrToken['token'] ,
    "client: mobi",
    "content-type: multipart/form-data; boundary=---011000010111000001101001",
    "x-authorization: $x_auth"
  ),
));
echo "$obs_key: [$message_id] = $obs_value <BR>";
// echo "$curlOpt_url, $x_auth<BR>";
$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
}

?>
</BR></div></body></html>