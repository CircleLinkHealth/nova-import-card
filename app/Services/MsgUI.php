<?php namespace App\Services;

use App\Activity;
use App\WpUser;
use App\WpUserMeta;
use DB;
use DateTime;
use DateTimeZone;

class MsgUI {


	function getForm($arrBio = array(), $date, $offset = null)
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


		// the actual form
		$formOutput .= "\n<div class='col-sm-4'>";
		if($arrBio['ReturnFieldType'] != 'None') {
			$formOutput .= "\n<input class='form-control' type='hidden' name='action' value='save_app_obs'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='SUBMIT' value='OBS'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='obs_key' value='" . $arrBio['Obs_Key'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='msg_id' value='" . $arrBio['MessageID'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='parent_id' value='" . $arrBio['ParentID'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='obs_date' value='" . $date . date(" H:i:s") . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnFieldType' value='" . $arrBio['ReturnFieldType'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeLow' value='" . $arrBio['ReturnDataRangeLow'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeHigh' value='" . $arrBio['ReturnDataRangeHigh'] . "'>";
			$formOutput .= "\n<input class='form-control' type='hidden' name='ReturnValidAnswers' value='" . $arrBio['ReturnValidAnswers'] . "'>";
			$formOutput .= "";
			$type = null;
			switch ($arrBio['ReturnFieldType']) {
				case 'Range':
					$type = "type='range' data-type='" . $arrBio['ReturnFieldType'] . "' min='" . $arrBio['ReturnDataRangeLow'] . "' max='" . $arrBio['ReturnDataRangeHigh'] . "' value='0'";
					$formOutput .= "\n<input $type id='obs_value' name='obs_value' value='" . $arrBio['PatientAnswer'] . "' REQUIRED>";
					break;
				case 'List':
					$formOutput .= "\n" . '<select name="obs_value" id="obs_value" data-role="slider">
<option value="N">No</option>
<option value="Y">Yes</option>
</select>';
					break;
				default;
					$formOutput .= "<input $type class='form-control col-sm-1' id='obs_value' name='obs_value' value='" . $arrBio['PatientAnswer'] . "' REQUIRED>";

			}
			$formOutput .= "<div><button class='btn btn-primary' type='submit'>SEND</button></div><br>\n";
		}
		$formOutput .= "</div>";

		// display answer if already given
		if ($arrBio['ReturnFieldType'] == 'None' || $arrBio['PatientAnswer'] ) {
			if ($arrBio['PatientAnswer'] )	$formOutput .= "<div class='col-sm-3 alert alert-success'>You Answered: " . $arrBio['PatientAnswer'] . " @ <small>". date('h:i:s A',strtotime($arrBio['ResponseDate'])) . "</small></div>\n";
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



	public function addAppSimCodeToCP($cpFeed) {
		$msgUI = new MsgUI;
		if(!empty($cpFeed['CP_Feed'])) {
			foreach ($cpFeed['CP_Feed'] as $key => $value) {
				$cpFeedSections = array('Biometric', 'DMS', 'Symptoms', 'Reminders');
				foreach ($cpFeedSections as $section) {
					foreach ($cpFeed['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio) {
						$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['formHtml'] = $msgUI->getForm($arrBio, $value['Feed']['FeedDate'], null);
						//echo($msgUI->getForm($arrBio,null));

						if (isset($arrBio['Response'])) {
							//echo($msgUI->getForm($arrBio['Response'],' col-lg-offset-1'));
							$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['formHtml'] = $msgUI->getForm($arrBio['Response'], $value['Feed']['FeedDate'], ' col-lg-offset-1');
							if (isset($arrBio['Response']['Response'])) {
								//echo($msgUI->getForm($arrBio['Response']['Response'],' col-lg-offset-3'));
								$cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response']['Response']['formHtml'] = $msgUI->getForm($arrBio['Response']['Response'], $value['Feed']['FeedDate'], ' col-lg-offset-1');
							}
						}
					}
				}
			}
		}
		return $cpFeed;
	}

}
