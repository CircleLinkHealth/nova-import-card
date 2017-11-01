<?php namespace App\Services;

use App\CPRulesUCP;
use App\Observation;
use DB;

class CareplanService
{

    var $wpUser; // user model
    var $programId;
    var $msgLanguageType = 'APP_EN';
    var $date; // date yyyy-mm-dd

    /**
     * Get Careplan
     *
     * @param  array  $wpUser
     * @param  array  $dates
     * @return array
     */
    public function getCareplan($wpUser, $dates)
    {
        // set universal user / vars
        $this->wpUser = $wpUser;
        $this->programId = $wpUser->program_id;

        // start feed
        $feed = array(
            "User_ID"  => $this->wpUser->id,
            "Comments" => "All data string are variable, DMS quantity and type of messages will change daily for each patient. Messages with Return Responses can nest. Message Content will have variable fields filled in by CPM and can vary between each patient. Message quantities will vary from day to day.",
            "Data"     => array(
                "Version" => "2.1",
                "EventDateTime" => date('Y-m-d H:i:s')),
            "CP_Feed"  => array(),
        );

        $i = 0;
        // loop through dates
        foreach ($dates as $date) {
            // set date
            $this->date = $date;

            // instantiate feed for date
            $feed["CP_Feed"][$i] = array(
                "Feed" => array(
                    "FeedDate" => $date,
                    "Messages" => array(),
                    "DMS" => array(),
                    "Reminders" => array(),
                    "Biometric" => array(),
                    "Symptoms" => array())
            );

            // DSM
            $feed["CP_Feed"][$i]['Feed']["DMS"] = $this->setObsDMS();

            // Reminders
            $feed["CP_Feed"][$i]['Feed']["Reminders"] = $this->setObsReminders();

            // Biometric
            $feed["CP_Feed"][$i]['Feed']["Biometric"] = $this->setObsBiometric();

            // Symptoms
            $feed["CP_Feed"][$i]['Feed']["Symptoms"] = $this->setObsSymptoms();
            $i++;
        }
        return $feed;
    }



    /**
     * @return array
     */
    private function setObsDMS()
    {
        $dmsMsgIds = $this->getScheduledDMS();
        $dmsObs = array();
        $i = 0;
        foreach ($dmsMsgIds as $dmsMsgId) {
            $observation = Observation::where('obs_message_id', '=', $dmsMsgId)
                ->where('user_id', '=', $this->wpUser->id)
                ->where('obs_unit', '!=', 'scheduled')
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'outbound')
                ->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", array())
                ->orderBy('obs_date', 'desc')
                ->first();
            if (!empty($observation) && $observation->comment_id != 0) {
                $dmsObs[$i] = $this->renderCommentThread($dmsMsgId, $observation->comment_id);
            } else {
                $dmsObs[$i] = $this->renderCommentThread($dmsMsgId, 0);
            }
            $i++;
        }

        // get answer for all adherence responses for day
        $msgChooser = new MsgChooser;
        $msgCPRules = new MsgCPRules;
        $msgSubstitutions = new MsgSubstitutions;
        $responseMsgId = $msgChooser->fxAlgorithmicForApp($this->wpUser->program_id, $this->wpUser->id, $this->date);

        if (!empty($responseMsgId)) {
            $qsType = $msgCPRules->getQsType($responseMsgId, $this->wpUser->id);
            $currQuestionInfo = $msgCPRules->getQuestion(
                $responseMsgId,
                $this->wpUser->id,
                $this->msgLanguageType,
                $this->programId,
                $qsType
            );
            if ($currQuestionInfo) {
                if (isset($currQuestionInfo->message)) {
                    $currQuestionInfo->message = $msgSubstitutions->doSubstitutions(
                        $currQuestionInfo->message,
                        $this->programId,
                        $this->wpUser->id
                    );
                } else {
                    $currQuestionInfo->message = '-';
                }
                // add to feed
                $dmsObs[$i - 1]['Response'][0] = array(
                    "MessageID" => $currQuestionInfo->msg_id,
                    "Obs_Key" => $currQuestionInfo->obs_key,
                    "ParentID" => 0,
                    "MessageIcon" => $currQuestionInfo->icon,
                    "MessageCategory" => $currQuestionInfo->category,
                    "MessageContent" => $currQuestionInfo->message,
                    "ReturnFieldType" => $currQuestionInfo->qtype,
                    "ReturnDataRangeLow" => $currQuestionInfo->low,
                    "ReturnDataRangeHigh" => $currQuestionInfo->high,
                    "ReturnValidAnswers" => '',
                    "PatientAnswer" => '',
                    "ReadingUnit" => '',
                    "ResponseDate" => ''
                );
            }
        }
        return $this->sortObs($dmsObs);
    }

    /**
     * @param $programId
     * @param $userId
     * @param $date
     * @param $obsKey
     *
     * @return array
     */
    public function getScheduledDMS()
    {
        // first get all active items
        $ucp = CPRulesUCP::where('user_id', '=', $this->wpUser->id)
            ->whereHas('item', function ($q) {
                $q->whereHas('pcp', function ($q2) {
                    $q2->where(function ($query) {
                        $query->orWhere('section_text', '=', 'Lifestyle to Monitor');
                        $query->orWhere('section_text', '=', 'Medications to Monitor');
                    });
                });
                $q->has('question');
            })
            ->with('item')
            ->with('item.question')
            ->with('item.question.questionSets')
            ->where('meta_value', '=', 'Active')
            ->get();
        //dd($ucp);
        $msgIds = [];
        $today = date('N', strtotime($this->date));
        if ($ucp->count() > 0) {
            foreach ($ucp as $ucpItem) {
                //echo $ucpItem->items_id . '['. $ucpItem->item->items_parent .']-'. $ucpItem->meta_key . '<br>';
                if ($ucpItem->meta_key == 'status') {
                    // get contact days for item
                    $ucpItemContactDays = CPRulesUCP::where('user_id', '=', $this->wpUser->id)
                        ->whereHas('item', function ($query) use
                            (
                            $ucpItem
                        ) {
                            $query->where('items_parent', '=', $ucpItem->items_id);
                            $query->where('items_text', '=', 'Contact Days');
                        })
                        ->first();

                    // add if contact days for today
                    if ($ucpItem->meta_value == 'Active' && $ucpItem->item->question && (strpos(
                        $ucpItemContactDays->meta_value,
                        $today
                    ) !== false)
                    ) {
                        //echo 'obs_key = '.$ucpItem->item->question->obs_key . '<br>';
                        $msgIds[] = $ucpItem->item->question->msg_id;
                    }
                }
            }
        }

        return $msgIds;
    }

    private function renderCommentThread($msgId, $commentId = 0)
    {
        $msgCPRules = new MsgCPRules;
        $msgSubstitutions = new MsgSubstitutions;
        $reportsService = new ReportsService;
        // for unanswered:
        if (empty($commentId)) {
            $qsType = $msgCPRules->getQsType($msgId, $this->wpUser->id);
            $currQuestionInfo = $msgCPRules->getQuestion(
                $msgId,
                $this->wpUser->id,
                $this->msgLanguageType,
                $this->programId,
                $qsType
            );
            $currQuestionInfo->message = $msgSubstitutions->doSubstitutions(
                $currQuestionInfo->message,
                $this->programId,
                $this->wpUser->id
            );
            //echo $msgId .'-'. $this->wpUser->id .'-'. $this->msgLanguageType .'-'. $this->programId .'-'. $qsType."<br><BR>".PHP_EOL;
            //var_dump($currQuestionInfo);
            //echo "<br><BR>".PHP_EOL;
            // add to feed
            $obsArr = array(
                "MessageID" => $currQuestionInfo->msg_id,
                "Obs_Key" => $currQuestionInfo->obs_key,
                "ParentID" => 0,
                "MessageIcon" => $currQuestionInfo->icon,
                "MessageCategory" => $currQuestionInfo->category,
                "MessageContent" => $currQuestionInfo->message,
                "ReturnFieldType" => $currQuestionInfo->qtype,
                "ReturnDataRangeLow" => $currQuestionInfo->low,
                "ReturnDataRangeHigh" => $currQuestionInfo->high,
                "ReturnValidAnswers" => $currQuestionInfo->valid_answers,
                "PatientAnswer" => '',
                "ReadingUnit" => '',
                "ResponseDate" => ''
            );
            return $obsArr;
        }
        // get all observations for message_thread
        $observations = Observation::where('comment_id', '=', $commentId)
            ->orderBy('sequence_id', 'asc')
            ->get();
        $obsArr = array();
        if ($observations->count() > 0) {
            $o = 0;
            $numOutbounds = 0;
            foreach ($observations as $observation) {
                // skip outbounds except for last response
                if ($msgId == 'CF_HSP_10') {
                    //echo(($o+$numOutbounds) . '-' . ($observations->count()-1)).PHP_EOL;
                    if (($o+$numOutbounds) != ($observations->count()-1) && $observation->obs_unit == 'outbound') {
                        $numOutbounds++;
                        continue 1;
                    }
                }
                //obtain message type
                $qsType = $msgCPRules->getQsType($observation->obs_message_id, $this->wpUser->id);
                $currQuestionInfo = $msgCPRules->getQuestion(
                    $observation->obs_message_id,
                    $this->wpUser->id,
                    $this->msgLanguageType,
                    $this->programId,
                    $qsType
                );
                if ($currQuestionInfo) {
                    // convert y/n to Yes/No for oab @todo fix this?
                    if (strtolower($observation->obs_value) == 'y') {
                        $observation->obs_value = 'Yes';
                    } else if (strtolower($observation->obs_value) == 'n') {
                        $observation->obs_value = 'No';
                    }
                    if (isset($currQuestionInfo->message)) {
                        $currQuestionInfo->message = $msgSubstitutions->doSubstitutions(
                            $currQuestionInfo->message,
                            $this->programId,
                            $this->wpUser->id
                        );
                    } else {
                        $currQuestionInfo->message = '-';
                    }
                    // add to feed
                    $tmpCommentId = $commentId;
                    if ($o == 0) {
                        $tmpCommentId = 0;
                    }
                    $obsTemp = array(
                        "MessageID" => $currQuestionInfo->msg_id,
                        "Obs_Key" => $currQuestionInfo->obs_key,
                        "ParentID" => $tmpCommentId,
                        "MessageIcon" => $currQuestionInfo->icon,
                        "MessageCategory" => $currQuestionInfo->category,
                        "MessageContent" => $currQuestionInfo->message,
                        "ReturnFieldType" => $currQuestionInfo->qtype,
                        "ReturnDataRangeLow" => $currQuestionInfo->low,
                        "ReturnDataRangeHigh" => $currQuestionInfo->high,
                        "ReturnValidAnswers" => $currQuestionInfo->valid_answers,
                        "PatientAnswer" => $observation->obs_value,
                        "ReadingUnit" => $reportsService->biometricsUnitMapping(str_replace('_', ' ', $currQuestionInfo->obs_key)),
                        "ResponseDate" => $observation->obs_date
                    );
                    if ($o == 0) {
                        $obsArr = $obsTemp;
                    } else if ($o == 1) {
                        $obsArr['Response'][0] = $obsTemp;
                    } else if ($o == 2) {
                        $obsArr['Response'][0]['Response'][0] = $obsTemp;
                    } else if ($o == 3) {
                        $obsArr['Response'][0]['Response'][0]['Response'][0] = $obsTemp;
                    } else if ($o == 4) {
                        $obsArr['Response'][0]['Response'][0]['Response'][0]['Response'][0] = $obsTemp;
                    }
                }
                $o++; // +1 bioObs
            }
        }
        if ($msgId == 'CF_HSP_10') {
            //dd($obsArr);
        }
        return $obsArr;
    }

    public function sortObs($observations)
    {
        // bypass for now
        return $observations;
        $obsByDate = array(); // key => obs array where key is the ResponseDate
        $obsUnanswered = array(); // array of obs where ResponseDate is blank
        $obsSorted = array(); // resulting sorted obs
        foreach ($observations as $tmpObs) {
            if (strlen($tmpObs['ResponseDate']) > 6) {
                $obsByDate[$tmpObs['ResponseDate']] = $tmpObs;
            } else {
                $obsUnanswered[] = $tmpObs;
            }
        }
        // sort by date array keys
        ksort($obsByDate);
        $i = 0;
        foreach ($obsUnanswered as $obs) {
            $obsSorted[$i] = $obs;
            $i++;
        }
        foreach ($obsByDate as $date => $obs) {
            $obsSorted[$i] = $obs;
            $i++;
        }

        return $obsSorted;
    }

    /**
     * @return array
     */
    private function setObsReminders()
    {
        $hspObs = [];

        // get all users ucp
        $ucpItems = CPRulesUCP::where('user_id', '=', $this->wpUser->id)->get();

        // find hsp items
        $contactDays = '';
        $status = 'Inactive';
        foreach ($ucpItems as $ucpItem) {
            if (isset($ucpItem->item->pcp->section_text)) {
                if ($ucpItem->item->pcp->section_text == 'Transitional Care Management'
                    && $ucpItem->item->items_text == 'Track Care Transitions'
                ) {
                    $status = $ucpItem->meta_value;
                }
            }
        }

        if ($status == 'Active') {
            $observation = Observation::where('obs_message_id', '=', 'CF_HSP_10')
                ->where('user_id', '=', $this->wpUser->id)
                ->where('obs_unit', '!=', 'scheduled')
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'outbound')
                ->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", [])
                ->orderBy('obs_date_gmt', 'desc')
                ->first();
            //dd($observation);
            if (!empty($observation)) {
                $hspObs[0] = $this->renderCommentThread('CF_HSP_10', $observation->comment_id);
            } else {
                $hspObs[0] = $this->renderCommentThread('CF_HSP_10', false);
            }
        }

        return $hspObs;
    }

    /**
     * @return array
     */
    private function setObsBiometric()
    {
        $msgUser = new MsgUser;
        $bioMsgIds = [];
        $userInfo = $msgUser->get_users_data($this->wpUser->id, 'id', $this->programId, true);
        if (!empty($userInfo[$this->wpUser->id]['usermeta']['user_care_plan'])) {
            $userInfoUCP = $userInfo[$this->wpUser->id]['usermeta']['user_care_plan'];
            // loop through care plan
            foreach ($userInfoUCP as $obsKey => $ucpItem) {
                if (isset($ucpItem['parent_status'])) {
                    // if active
                    if ($ucpItem['parent_status'] == 'Active') {
                        $query = DB::connection('mysql_no_prefix')->table('rules_questions')->select('msg_id')
                            ->where('obs_key', "=", $obsKey);
                        $questionInfo = $query->first();
                        if ($questionInfo) {
                            $msgId = $questionInfo->msg_id;
                            $bioMsgIds[] = $msgId;
                        }
                    }
                }
            }
        }
        $bioObs = [];
        $i = 0;
        foreach ($bioMsgIds as $bioMsgId) {
            $observation = Observation::where('obs_message_id', '=', $bioMsgId)
                ->where('user_id', '=', $this->wpUser->id)
                ->where('obs_unit', '!=', 'scheduled')
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'outbound')
                ->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", [])
                ->orderBy('obs_date_gmt', 'desc')
                ->first();
            if (!empty($observation) && $observation->comment_id != 0) {
                $bioObs[$i] = $this->renderCommentThread($bioMsgId, $observation->comment_id);
            } else {
                $bioObs[$i] = $this->renderCommentThread($bioMsgId, 0);
            }
            $i++;
        }

        return $this->sortObs($bioObs);
    }

    /**
     * @return array
     */
    private function setObsSymptoms()
    {
        $symMsgIds = $this->getScheduledSymptoms();
        $symObs = [];
        $i = 0;
        $answered = '';
        foreach ($symMsgIds as $symMsgId) {
            $observation = Observation::where('obs_message_id', '=', $symMsgId)
                ->where('user_id', '=', $this->wpUser->id)
                ->where('obs_unit', '!=', 'scheduled')
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'outbound')
                ->whereRaw("obs_date BETWEEN '" . $this->date . " 00:00:00' AND '" . $this->date . " 23:59:59'", [])
                ->orderBy('obs_date_gmt', 'desc')
                ->first();
            if (!empty($observation) && $observation->comment_id != 0) {
                $answered = 'Yes';
                $symObs[$i] = $this->renderCommentThread($symMsgId, $observation->comment_id);
            } else {
                $symObs[$i] = $this->renderCommentThread($symMsgId, 0);
            }
            $i++;
        }
        if (!empty($symObs)) {
            $symObs = [
                0 => [
                    "MessageID"           => "CF_SYM_MNU_10",
                    "Obs_Key"             => "Severity",
                    "ParentID"            => "603",
                    "MessageIcon"         => "question",
                    "MessageCategory"     => "Question",
                    "MessageContent"      => "Any <b>symptoms</b> Today?",
                    "ReturnFieldType"     => '',
                    "ReturnDataRangeLow"  => '',
                    "ReturnDataRangeHigh" => '',
                    "ReturnValidAnswers"  => '',
                    "PatientAnswer"       => $answered,
                    "ReadingUnit"         => '',
                    "ResponseDate"        => '',
                    "Response"            => $this->sortObs($symObs),
                ],
            ];
        }

        return $symObs;
    }

    /**
     * @return array
     */
    public function getScheduledSymptoms()
    {
        $ucp = CPRulesUCP::where('user_id', '=', $this->wpUser->id)
            ->whereHas('item', function ($q) {
                $q->whereHas('pcp', function ($q2) {
                    $q2->where('section_text', '=', 'Symptoms to Monitor');
                });
            })
            ->where('meta_value', '=', 'Active')
            ->get();
        $msgIds = array();
        if ($ucp->count() > 0) {
            foreach ($ucp as $ucpItem) {
                if ($ucpItem->item->question) {
                    $msgIds[] = $ucpItem->item->question->msg_id;
                }
            }
        }
        return $msgIds;
    }
}
