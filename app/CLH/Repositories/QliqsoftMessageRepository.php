<?php namespace App\CLH\Repositories;

use App\QliqsoftMessageLog;

class QliqsoftMessageRepository {

    /**
     * Save the response from the qliq Api in the DB
     * @param $args
     */
    public function saveResponseToDb($args)
    {
        $log = new QliqsoftMessageLog();
        $log->to = $args['to'];
        $log->message = $args['text'];
        $log->status = $args['status'];
        $log->conversation_id = $args['conversation_id'];
        $log->message_id = $args['message_id'];
        $log->save();
    }


    /**
     * Get the conversation id from the logs, if one exists.
     *
     * @param $to
     * @return string|false
     */
    public function getConversationId($to)
    {
        $log = QliqsoftMessageLog::whereTo($to)->first();

        return empty($log->conversation_id) ? false : $log->conversation_id;
    }

}