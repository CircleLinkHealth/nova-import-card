<?php namespace App\Services;

use App\Activity;
use App\WpUser;
use App\WpUserMeta;
use DB;
use DateTime;
use DateTimeZone;

class MsgUI {


	function getForm($arrBio = array(), $offset = null)
	{
		$formOutput ="";
		$formOutput .= "<form action='' method=post>\n";
		$formOutput .= "<div class='row'>\n";
		$msgIcon = array('icon' => '', 'color' => '');
		if(isset($arrBio['MessageIcon'])) {
			$msgIcon = $this->getMsgIcon($arrBio['MessageIcon']);
		} else {
			//dd($arrBio);
		}
		$formOutput .= "<hr><div class='col-sm-1$offset'><i style='color:". $msgIcon['color'] ."' class='fa fa-2x fa-". $msgIcon['icon'] ."'></i></div>\n";
		$formOutput .= "<div class='col-sm-4'>" . $arrBio['MessageContent'] . "</div>\n";
		// $formOutput .= " [" . $arrBio['MessageID'] . " | `" . $arrBio['Obs_Key'] . "` | " . date('Y-m-d H:i:s O)') . "] <BR>";
		if ($arrBio['ReturnFieldType'] == 'None' || $arrBio['PatientAnswer'] ) {
			if ($arrBio['PatientAnswer'] )	$formOutput .= "<div class='col-sm-6'>You Answered: " . $arrBio['PatientAnswer'] . " @ <small>". date('h:i:s A',strtotime($arrBio['ResponseDate'])) . "</small></div>\n";
		} else {
			$formOutput .= "\n<input class='form-control' type='hidden' name='SUBMIT' value='OBS'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='Obs_Key' value='". $arrBio['Obs_Key'] ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='MessageID' value='". $arrBio['MessageID'] ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='Obs_Date' value='". date("Y-m-d H:i:s") ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnFieldType' value='". $arrBio['ReturnFieldType'] ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeLow' value='". $arrBio['ReturnDataRangeLow'] ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeHigh' value='". $arrBio['ReturnDataRangeHigh'] ."'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnValidAnswers' value='". $arrBio['ReturnValidAnswers'] ."'>";
			$formOutput .= "";
			$formOutput .= "\n<div class='col-sm-4'>";
			$type = null;
			switch ($arrBio['ReturnFieldType']) {
				case 'Range':
					$type = "type='range' data-type='". $arrBio['ReturnFieldType']. "' min='" .$arrBio['ReturnDataRangeLow']. "' max='" .$arrBio['ReturnDataRangeHigh']. "' value='0' data-theme='b' data-track-theme='c'";
					$formOutput .= "\n<input $type id='obs_val' name='obs_val' value='".$arrBio['PatientAnswer']."' REQUIRED>";
					break;
				case 'List':
					$formOutput .= "\n".'<select name="obs_val" id="obs_val" data-role="slider">
	<option value="N">No</option>
	<option value="Y">Yes</option>
</select>';
					break;
				default;
					$formOutput .= "<input $type class='form-control col-sm-1' id='obs_val' name='obs_val' value='".$arrBio['PatientAnswer']."' REQUIRED>";

			}
			$formOutput .= "<div class='ui-block-a'><button data-theme='b' class='btn btn-primary col-sm-1' type='submit'>SEND</button></div><br>\n";
			$formOutput .= "</div>";
		}
		$formOutput .= "</div>\n";
		$formOutput .= "</form>\n";

		return $formOutput;
	}

	function getMsgIcon($msgIcon)
	{
		$color = 'Blue';
		switch ($msgIcon) {
			case 'bp';
			case 'bs';
				$icon = 'clock-o';
				break;
			case 'wt':
				$icon = 'balance-scale';
				break;
			case 'cs':
				$icon = 'ban';
				break;
			case 'call':
				$icon = 'phone';
				break;
			case 'hsp':
				$icon = 'hospital-o';
				break;
			case 'emergency':
				$icon = 'exclamation-circle';
				$color = 'Red';
				break;
			case 'question':
				$icon = 'question-circle';
				break;
			case 'reminder':
				$icon = 'sticky-note-o';
				break;
			case 'info':
			case 'tip':
				$icon = 'info-circle';
				break;

			default:
				$icon = 'dICO';
				break;
		}
		$msgIcon = array('color' => $color,
			'icon' => $icon);
		return $msgIcon;

	}

}
