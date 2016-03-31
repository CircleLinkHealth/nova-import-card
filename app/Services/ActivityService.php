<?php namespace App\Services;

use App\Activity;
use App\User;
use App\UserMeta;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class ActivityService
{
    /**
     * Get total activity for a range of two Carbon dates.
     *
     * @param $userId
     * @param Carbon $from
     * @param Carbon $to
     * @return mixed
     */
    public function getTotalActivityTimeForRange($userId, Carbon $from, Carbon $to)
    {
        $acts = new Collection( DB::table( 'lv_activities' )
            ->select( DB::raw( 'id,provider_id,logged_from,DATE(performed_at), type, SUM(duration) as duration' ) )
            ->whereBetween( 'performed_at', [
                $from, $to
            ] )
            ->where( 'patient_id', $userId )
            ->where( function ($q) {
                $q->where( 'logged_from', 'activity' )
                    ->Orwhere( 'logged_from', 'manual_input' )
                    ->Orwhere( 'logged_from', 'pagetimer' );
            } )
            ->groupBy( DB::raw( 'provider_id, DATE(performed_at),type' ) )
            ->orderBy( 'performed_at', 'desc' )
            ->get()
        );

        return $acts->map( function ($act) {
            return $act->duration;
        } )->sum();
    }

    public function getTotalActivityTimeForMonth($userId, $month = false, $year = false)
    {
        // if no month, set to current month
        if ( !$month ) {
            $month = date( 'm' );
        }
        if ( !$year ) {
            $year = date( 'Y' );
        }

        $time = Carbon::createFromDate( $year, $month, 15 );
        $start = $time->startOfMonth()->format( 'Y-m-d' ) . ' 00:00:00';
        $end = $time->endOfMonth()->format( 'Y-m-d' ) . ' 12:59:59';
        $month_selected = $time->format( 'm' );
        $month_selected_text = $time->format( 'F' );
        $year_selected = $time->format( 'Y' );

        $acts = DB::table( 'lv_activities' )
            ->select( DB::raw( 'id,provider_id,logged_from, performed_at, type, SUM(duration) as duration' ) )
            ->whereBetween( 'performed_at', [
                $start, $end
            ] )
            ->where( 'patient_id', $userId )
            ->where( function ($q) {
                $q->where( 'logged_from', 'activity' )
                    ->Orwhere( 'logged_from', 'manual_input' )
                    ->Orwhere( 'logged_from', 'pagetimer' );
            } )
            ->groupBy( DB::raw( 'provider_id, performed_at,type' ) )
            ->orderBy( 'performed_at', 'desc' )
            ->get();

        $totalDuration = 0;
        foreach ( $acts as $act ) {
            $totalDuration = ($totalDuration + $act->duration);
        }

        /*
        $totalDuration = Activity::where( \DB::raw('MONTH(performed_at)'), '=', $month )->where( \DB::raw('YEAR(performed_at)'), '=', $year )->where( 'patient_id', '=', $userId )->sum('duration');
        */
        return $totalDuration;
    }

    public function reprocessMonthlyActivityTime($userIds = false, $month = false, $year = false)
    {
        // if no month, set to current month
        if ( !$month ) {
            $month = date( 'm' );
        }
        if ( !$year ) {
            $year = date( 'Y' );
        }

        if ( $userIds ) {
            // cast userIds to array if string
            if ( !is_array( $userIds ) ) {
                $userIds = array($userIds);
            }
            $wpUsers = User::whereIn( 'id', $userIds )->orderBy( 'ID', 'desc' )->get();
        }
        else {
            // get all users
            $wpUsers = User::orderBy( 'ID', 'desc' )->get();
        }

        if ( !empty($wpUsers) ) {
            // loop through each user
            foreach ( $wpUsers as $wpUser ) {
                // get all activities for user for month
                $totalDuration = $this->getTotalActivityTimeForMonth( $wpUser->ID, $month, $year );

                // update user_meta with total
                $userMeta = UserMeta::where( 'user_id', '=', $wpUser->ID )
                    ->where( 'meta_key', '=', 'cur_month_activity_time' )->first();
                if ( !$userMeta ) {
                    // add in initial user meta: cur_month_activity_time
                    $newUserMetaAttr = array(
                        'user_id' => $wpUser->ID,
                        'meta_key' => 'cur_month_activity_time',
                        'meta_value' => $totalDuration,
                    );
                    $newUserMeta = UserMeta::create( $newUserMetaAttr );
                    //echo "<pre>CREATED";var_dump($newUserMeta);echo "</pre>";die();
                }
                else {
                    // update existing user meta: cur_month_activity_time
                    $userMeta = UserMeta::where( 'user_id', '=', $wpUser->ID )
                        ->where( 'meta_key', '=', 'cur_month_activity_time' )
                        ->update( array('meta_value' => $totalDuration) );
                    //echo "<pre>UPDATED";var_dump($totalDuration);echo "</pre>";die();
                }
            }
        }
        return true;
    }

    /**
     * @param $careteam
     * @param $url
     * @param $performed_at
     * @param $user_id
     * @param $logger_name
     * @param $newNoteFlag (checks whether it's a new note or an old one)
     * @param bool $admitted_flag
     * @return bool
     */
    public function sendNoteToCareTeam(&$careteam, $url, $performed_at, $user_id, $logger_name, $newNoteFlag, $admitted_flag = false)
    {

        /*
         *  New note: "Please see new note for patient [patient name]: [link]"
         *  Old/Fw'd note: "Please see forwarded note for patient [patient  name], created on [creation date] by [note creator]: [link]
         */

        $user = User::find( $user_id );
        for ( $i = 0; $i < count( $careteam ); $i++ ) {
            $provider_user = User::find( $careteam[ $i ] );
            debug( $provider_user );
            $email = $provider_user->user_email;
            $performed_at = Carbon::parse( $performed_at )->toFormattedDateString();
            $data = array(
                'patient_name' => $user->display_name,
                'url' => $url,
                'time' => $performed_at,
                'logger' => $logger_name
            );

            if ( $newNoteFlag || $admitted_flag ) {
                $email_view = 'emails.newnote';
                $email_subject = 'Urgent Patient Note from CircleLink Health';
            }
            else {
                $email_view = 'emails.existingnote';
                $email_subject = 'You have received a new note notification from CarePlan Manager';
            }
            Mail::send( $email_view, $data, function ($message) use ($email, $email_subject) {
                $message->from( 'no-reply@careplanmanager.com', 'CircleLink Health' );
                $message->to( $email )->subject( $email_subject );
            } );
        }
        return true;
//		dd(count(Mail::failures()));
//		if( count(Mail::failures()) > 0 ) {
//			return false;
//		} else {
//			return true;
//		}
    }
}
