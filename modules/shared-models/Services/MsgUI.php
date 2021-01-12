<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

class MsgUI
{
    public function addAppSimCodeToCP($cpFeed)
    {
        $msgUI = new MsgUI();
        if ( ! empty($cpFeed['CP_Feed'])) {
            foreach ($cpFeed['CP_Feed'] as $key => $value) {
                $cpFeedSections = ['Symptoms', 'Biometric', 'DMS', 'Reminders'];
                foreach ($cpFeedSections as $section) {
                    foreach ($cpFeed['CP_Feed'][$key]['Feed'][$section] as $keyBio => $arrBio) {
                        $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['formHtml'] = $msgUI->getForm($arrBio, $value['Feed']['FeedDate'], null);
                        //echo($msgUI->getForm($arrBio,null));
                        $r = 0;
                        while ($r <= 10) {
                            if (isset($arrBio['Response'][$r])) {
                                //echo($msgUI->getForm($arrBio['Response'],' col-lg-offset-1'));
                                $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response'][$r]['formHtml'] = $msgUI->getForm($arrBio['Response'][$r], $value['Feed']['FeedDate'], ' col-lg-offset-1');
                                if (isset($arrBio['Response'][$r]['Response'][0])) {
                                    //echo($msgUI->getForm($arrBio['Response']['Response'],' col-lg-offset-3'));
                                    $cpFeed['CP_Feed'][$key]['Feed'][$section][$keyBio]['Response'][$r]['Response'][0]['formHtml'] = $msgUI->getForm($arrBio['Response'][$r]['Response'][0], $value['Feed']['FeedDate'], ' col-lg-offset-2');
                                }
                            }
                            ++$r;
                        }
                    }
                }
            }
        }

        return $cpFeed;
    }

    public function getForm($arrBio = [], $date, $offset = null)
    {
        date_default_timezone_set('America/New_York');
        $formOutput = '';
        $formOutput .= "<form action='' method=post>\n";
        $formOutput .= "<div class='row'>\n";
        $msgIcon = ['icon' => '', 'color' => ''];
        if (isset($arrBio['MessageIcon'])) {
            $msgIcon = $this->getMsgIcon($arrBio['MessageIcon']);
        }
        //dd($arrBio);

        $formOutput .= "<hr><div class='col-sm-1${offset}'><i style='color:".$msgIcon['color']."' class='fa fa-2x fa-".$msgIcon['icon']."'></i></div>\n";
        $formOutput .= "<div class='col-sm-4'>".$arrBio['MessageContent']."</div>\n";
        // $formOutput .= " [" . $arrBio['MessageID'] . " | `" . $arrBio['Obs_Key'] . "` | " . date('Y-m-d H:i:s O)') . "] <BR>";

        // the actual form
        $formOutput .= "\n<div class='col-sm-4'>";
        if ('None' != $arrBio['ReturnFieldType']) {
            $formOutput .= "\n<input class='form-control' type='hidden' name='action' value='save_app_obs'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='SUBMIT' value='OBS'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='obs_key' value='".$arrBio['Obs_Key']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='msg_id' value='".$arrBio['MessageID']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='parent_id' value='".$arrBio['ParentID']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='obs_date' value='".$date.date(' H:i:s')."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='ReturnFieldType' value='".$arrBio['ReturnFieldType']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeLow' value='".$arrBio['ReturnDataRangeLow']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='ReturnDataRangeHigh' value='".$arrBio['ReturnDataRangeHigh']."'>";
            $formOutput .= "\n<input class='form-control' type='hidden' name='ReturnValidAnswers' value='".$arrBio['ReturnValidAnswers']."'>";
            $formOutput .= '';
            $type = null;
            switch ($arrBio['ReturnFieldType']) {
                case 'Range':
                    // custom for blood pressure
                    if ('Blood_Pressure' == $arrBio['Obs_Key']) {
                        if (false !== strpos($arrBio['ReturnDataRangeLow'], '/')) {
                            $lowPieces = explode('/', $arrBio['ReturnDataRangeLow']);
                        }
                        if (false !== strpos($arrBio['ReturnDataRangeHigh'], '/')) {
                            $highPieces = explode('/', $arrBio['ReturnDataRangeHigh']);
                        }
                        $type = "type='range' data-type='".$arrBio['ReturnFieldType']."' min='".$lowPieces[0]."' max='".$highPieces[0]."' value='0'";
                        $formOutput .= "\n<input ${type} id='obs_value' name='obs_value' value='".$arrBio['PatientAnswer']."' REQUIRED>";
                        $type = "type='range' data-type='".$arrBio['ReturnFieldType']."' min='".$lowPieces[1]."' max='".$highPieces[1]."' value='0'";
                        $formOutput .= "\n<input ${type} id='obs_value' name='obs_value' value='".$arrBio['PatientAnswer']."' REQUIRED>";
                    } else {
                        $type = "type='range' data-type='".$arrBio['ReturnFieldType']."' min='".$arrBio['ReturnDataRangeLow']."' max='".$arrBio['ReturnDataRangeHigh']."' value='0'";
                        $formOutput .= "\n<input ${type} id='obs_value' name='obs_value' value='".$arrBio['PatientAnswer']."' REQUIRED>";
                    }
                    break;
                case 'List':
                    $formOutput .= "\n".'<select name="obs_value" id="obs_value" data-role="slider">';
                    if ( ! empty($arrBio['ReturnValidAnswers'])) {
                        $answers = explode(',', $arrBio['ReturnValidAnswers']);
                        if ( ! empty($answers)) {
                            foreach ($answers as $answer) {
                                $formOutput .= '<option value="'.$answer.'">'.$answer.'</option>';
                            }
                        }
                    } else {
                        $formOutput .= '<option value="N">No</option><option value="Y">Yes</option>';
                    }
                    $formOutput .= '</select>';
                    break;
                case 'Date':
                    $formOutput .= "<input ${type} class='form-control col-sm-1' id='obs_value' name='obs_value' value='".$arrBio['PatientAnswer']."' REQUIRED>";
                    // no break
                default:
                    $formOutput .= '';
            }
            $formOutput .= "<div><button class='btn btn-primary' type='submit'>SEND</button></div><br>\n";
        }
        $formOutput .= '</div>';

        // display answer if already given
        if ('None' == $arrBio['ReturnFieldType'] || (strlen($arrBio['PatientAnswer']) > 0)) {
            if ((strlen($arrBio['PatientAnswer']) > 0)) {
                $formOutput .= "<div class='col-sm-3 alert alert-success'>You Answered: ".$arrBio['PatientAnswer'].' @ <small>'.date('h:i:s A', strtotime($arrBio['ResponseDate']))."</small></div>\n";
            }
        }

        $formOutput .= "</div>\n";
        $formOutput .= "</form>\n";

        return $formOutput;
    }

    public function getMsgIcon($msgIcon)
    {
        $color = 'Blue';
        switch ($msgIcon) {
            case 'bp':
            case 'bs':
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
                $icon  = 'exclamation-circle';
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

        return ['color' => $color,
            'icon'      => $icon, ];
    }
}
