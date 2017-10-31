<?php namespace App\Services;

use App\User;
use App\UserMeta;
use App\Services\MsgCPRules;
use App\Services\MsgDelivery;
use App\Services\MsgUsers;
use DB;

/*
$this->_ci =& get_instance();
$this->_ci->load->model('cpm_1_7_rules_model','rules');
$this->_ci->load->model('cpm_1_7_smsdelivery_model','mailman');
$this->_ci->load->model('cpm_1_7_observation_model','obs');
*/

class MsgSubstitutions
{

    public function __construct()
    {
    } //construct

    public function doSubstitutions($strMessage, $provid = 0, $user_id = 0)
    {

        if (preg_match("/#Readings#/", $strMessage)) {
            $strMessage = $this->getReadingsText($provid, $user_id, $strMessage, 'EN');
        }
        if (preg_match("/#lecturas#/", $strMessage)) {
            $strMessage = $this->getReadingsText($provid, $user_id, $strMessage, 'ES');
        }

        if (preg_match("/#Reminder#/", $strMessage)) {
            $strMessage = $this->getReminderText($provid, $user_id, $strMessage, 'EN');
        }
        if (preg_match("/#Aviso#/", $strMessage)) {
            $strMessage = $this->getReminderText($provid, $user_id, $strMessage, 'ES');
        }

        if (preg_match("/#CONTACTTIME#/", $strMessage)) {
            $strMessage = $this->getContactTime($provid, $user_id, $strMessage);
        }

        return $strMessage;
    } //doSubstitutions

    // Replacement text for #Readings#
    private function getReadingsText($provid, $user_id, $strMessage, $strLang = 'EN')
    {

        /**
         * @todo    Need to replace hard coding to use db for replacements
         */

        $msgCPRules = new MsgCPRules;
        $msgDelivery = new MsgDelivery;

        $strReturn = '';

        if ($provid > 0 and $user_id > 0) {
            // get list of Reading for individual
            $arrList = $msgCPRules->getReadingDefaults($user_id, $provid);
            // echo '<br>Readings to send today: ';
            // print_r($arrList);
            if (!empty($arrList)) {
                $arrReadings = $msgCPRules->getReadings($provid, $user_id);
                // variables for scheduled RPT
                $tmpArr = [];
                $i = 0; // counter for tense of the word reading or readings depending on how many we send.

                foreach ($arrList as $row) {
                    // chech if biometric is active and can be sent today
                    if ((!empty($row->APActive) && $row->APActive == 'Active') or ($row->UActive == 'Active' and strpos($row->cdays, $row->today) !== false)) {
                        // build array of RPT scheduled for today.
                        $tmpArr[$i++][$row->msg_id] = $row->obs_key;

                        // only use open ones for message substitutions
                        if (empty($arrReadings) || strpos(serialize($arrReadings), $row->obs_key) === false) {
                            switch ($row->obs_key) {
                                case 'Blood_Pressure':
                                    if ($strLang == 'ES') {
                                        $strReturn .= 'presion arterial en reposo, ';
                                    } else {
                                        $strReturn .= 'resting blood pressure, ';
                                    }
                                    break;

                                case 'Blood_Sugar':
                                    if ($strLang == 'ES') {
                                        $strReturn .= 'el nivel de azucar en la sangre en ayunas, ';
                                    } else {
                                        $strReturn .= 'fasting blood sugar, ';
                                    }
                                    break;

                                case 'Cigarettes':
                                    if ($strLang == 'ES') {
                                        $strReturn .= 'numero de cigarrillos fumados, ';
                                    } else {
                                        $strReturn .= 'number of cigarettes smoked, ';
                                    }
                                    break;

                                case 'Weight':
                                    if ($strLang == 'ES') {
                                        $strReturn .= 'peso, ';
                                    } else {
                                        $strReturn .= 'weight, ';
                                    }
                                    break;

                                default:
                                    $strReturn .= strtolower(str_replace('_', ' ', $row->obs_key)).', ';
                                    break;
                            }
                        }
                    }
                }

                /*
                 * @todo SHOULD SCHEDULE RECORDS BE INSERTED HERE? no
                 *
                $commentId = $msgDelivery->writeOutboundSmsMessage($user_id,$tmpArr,'substitutionlibrary', 'scheduled',$provid);

                // echo '<br>Substitution Scheduled: <pre>';
                // print_r($tmpArr);

                // write out observation records
                //echo "<br>MsgSubstitution->var_dump";
                //var_dump($tmpArr);
                foreach ($tmpArr as $sequence => $value) {
                    foreach ($value as $msgId => $obsKey) {
                        // echo '<br>'.$key.': '.$key2.' -> '.$value2;
                        //flag obs_processor of response
                        $data = array(
                            'comment_id' => $commentId,
                            'sequence_id' => $sequence,
                            'user_id' => $user_id,
                            'obs_message_id' => $msgId,
                            'obs_key' =>  $obsKey,
                            'obs_method' => 'RPT',
                            'obs_date' => date('Y-m-d H:i:s', strtotime('00:00:01')),
                            'obs_date_gmt' => date("Y-m-d H:i:s", strtotime('00:00:01')),
                            'obs_unit' => 'scheduled'
                        );

                        // insert new observation record
                        $obs_id = DB::connection('mysql_no_prefix')->table('ma_'.$provid.'_observations')->insertGetId( $data );
                        echo "<br>MsgSubstitutions->getReadingsText() Created New Observation#=" . $obs_id;
                        //$obs_id = $this->_ci->obs->insert_observation($data, false, $provid);

                    }// foreach2
                }// foreach
                $strReturn = trim($strReturn, ', ');
                */
            }
        }

        //  check language
        if ($strLang == 'ES') {
            $strMessage = preg_replace('/#lecturas#/', $strReturn, $strMessage);
        } else {
            $strMessage = preg_replace('/#Readings#/', $strReturn, $strMessage);
        }

        switch ($i) {
            case 0:
                // if no readings are found do not send message
                $strMessage = '';
                break;

            case 1:
                // if one reading is found leave message as is
                break;

            default:
                // All others must be multiple responses
                // $strMessage = str_replace('reading', 'readings', $strMessage);
                break;
        }
        // echo '<hr><hr>Substitution: i: '.$i.' and message: '.$strMessage.'<hr><hr>';
        //echo "<br>MsgSubstitutions->getReadingsText, returning '$strMessage''";
        return $strMessage;
    }//fxCheckForReadings


    // Replacement text for #Readings#
    private function getReminderText($provid, $user_id, $strMessage, $strLang = 'EN')
    {
        if ($strLang == 'ES') {
            $strNewMessage = 'Por favor recuerde de enviar sus #lecturas# eviando RPT por mensaje de texto hoy.';
            $strNewMessage = $this->getReadingsText($provid, $user_id, $strNewMessage, $strLang);
            $strMessage = preg_replace('/#Aviso#/', $strNewMessage, $strMessage);
        } else {
            $strNewMessage = 'Please remember to send us your #Readings# by texting RPT today.';
            $strNewMessage = $this->getReadingsText($provid, $user_id, $strNewMessage, $strLang);
            $strMessage = preg_replace('/#Reminder#/', $strNewMessage, $strMessage);
        }


        return $strMessage;
    }

    // Replacement text for CONTACTTIME
    private function getContactTime($provid, $user_id, $strMessage)
    {
        $msgCPRules = new MsgCPRules;
        $strConfig = $msgCPRules->getUserConfig($provid, $user_id);
        $arrConfig = unserialize($strConfig);

        $strMessage = preg_replace('/#CONTACTTIME#/', $arrConfig['preferred_contact_time'], $strMessage);

        return $strMessage;
    } //getContactTime
}
