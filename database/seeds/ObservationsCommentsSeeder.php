<?php

use App\Comment;
use App\Observation;
use App\ObservationMeta;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ObservationsCommentsSeeder extends Seeder {

    var $days = 18;

    public function run()
    {
        $comments = DB::connection('mysql_no_prefix')
            ->table('wp_7_comments')
            ->whereRaw('comment_date > DATE_SUB(NOW(), INTERVAL '.$this->days.' DAY)')
            ->whereRaw('comment_ID NOT IN (SELECT legacy_comment_id FROM lv_comments)')
            ->orderBy('comment_ID', 'desc')
            //->limit(2)
            ->get();
        echo 'Found '.count($comments).' comments'.PHP_EOL;
        if(!empty($comments)) {
            foreach($comments as $comment) {
                // add comment to lv_comments
                $newComment = new Comment;
                $newComment->comment_post_ID = $comment->comment_post_ID;
                $newComment->comment_author = $comment->comment_author;
                $newComment->comment_author_email = $comment->comment_author_email;
                $newComment->comment_author_url = $comment->comment_author_url;
                $newComment->comment_content = $comment->comment_content;
                $newComment->comment_type = $comment->comment_type;
                $newComment->comment_parent = $comment->comment_parent;
                $newComment->user_id = $comment->user_id;
                $newComment->comment_author_IP = $comment->comment_author_IP;
                $newComment->comment_agent = $comment->comment_agent;
                $newComment->comment_date = $comment->comment_date;
                $newComment->comment_date_gmt = $comment->comment_date_gmt;
                $newComment->comment_approved = $comment->comment_approved;
                $newComment->legacy_comment_id = $comment->comment_ID;
                $newComment->save();
                echo 'Created lv_comment '.$newComment->id .PHP_EOL;
            }
        }

        $observations = DB::connection('mysql_no_prefix')
            ->table('ma_7_observations')
            ->select('ma_7_observations.*', 'lv_comments.id AS lv_comment_id')
            ->whereRaw('obs_id NOT IN (SELECT legacy_obs_id FROM lv_observations)')
            ->whereRaw('obs_date > DATE_SUB(NOW(), INTERVAL '.$this->days.' DAY)')
            ->leftJoin('lv_comments', 'ma_7_observations.comment_id', '=', 'lv_comments.legacy_comment_id')
            ->orderBy('obs_id', 'desc')
            //->limit(2)
            ->get();

        echo 'Found '.count($observations).' observations'.PHP_EOL;
        if(!empty($observations)) {
            foreach($observations as $observation) {
                // add observation to lv_observations
                $newObservation = new Observation;
                $newObservation->comment_id = $observation->lv_comment_id;
                $newObservation->sequence_id = $observation->sequence_id;
                $newObservation->user_id = $observation->user_id;
                $newObservation->obs_message_id = $observation->obs_message_id;
                $newObservation->obs_method = $observation->obs_method;
                $newObservation->obs_key = $observation->obs_key;
                $newObservation->obs_unit = $observation->obs_unit;
                $newObservation->obs_date = $observation->obs_date;
                $newObservation->obs_date_gmt = $observation->obs_date_gmt;
                $newObservation->legacy_obs_id = $observation->obs_id;
                $newObservation->save();
                echo 'Created lv_observation '.$newObservation->id .PHP_EOL;
            }
        }

        $obsMetas = DB::connection('mysql_no_prefix')
            ->table('ma_7_observationmeta')
            ->select('ma_7_observationmeta.*', 'lv_observations.id AS lv_obs_id')
            ->whereRaw('meta_id NOT IN (SELECT legacy_meta_id FROM lv_observationmeta)')
            ->join('lv_observations', 'ma_7_observationmeta.obs_id', '=', 'lv_observations.legacy_obs_id')
            ->whereRaw('obs_date > DATE_SUB(NOW(), INTERVAL '.$this->days.' DAY)')
            ->orderBy('meta_id', 'desc')
            //->limit()
            ->get();

        echo 'Found '.count($obsMetas).' observationmeta'.PHP_EOL;
        if(!empty($obsMetas)) {
            foreach($obsMetas as $obsMeta) {
                // add observation to lv_observations
                $newobsMeta = new ObservationMeta;
                $newobsMeta->comment_id = '';
                $newobsMeta->message_id = $obsMeta->message_id;
                $newobsMeta->meta_key = $obsMeta->meta_key;
                $newobsMeta->meta_value = $obsMeta->meta_value;
                $newobsMeta->obs_id= $obsMeta->lv_obs_id;
                $newobsMeta->legacy_meta_id = $obsMeta->meta_id;
                $newobsMeta->save();
                echo 'Created lv_observationmeta '.$newobsMeta->id .PHP_EOL;
            }
        }
    }

}