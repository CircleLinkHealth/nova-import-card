<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\CPRulesQuestions;
use App\Observation;
use App\ObservationMeta;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class DatamonitorService
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->time_start = 0;
        $this->time_end   = 0;
        $this->int_id     = 0;
        $this->client     = $client;
    }

    public function get_alert_msg_info(
        $alert_msg_id
    ) {
        if ('CF_AL_01' == $alert_msg_id) {
            $alert_sort_weight = 7;
            $alert_level       = 'warning';
        } else {
            if ('CF_AL_02' == $alert_msg_id) {
                $alert_sort_weight = 9;
                $alert_level       = 'danger';
            } else {
                if ('CF_AL_03' == $alert_msg_id) {
                    $alert_sort_weight = 9;
                    $alert_level       = 'danger';
                } else {
                    if ('CF_AL_04' == $alert_msg_id) {
                        $alert_sort_weight = 9;
                        $alert_level       = 'danger';
                    } else {
                        if ('CF_AL_05' == $alert_msg_id) {
                            $alert_sort_weight = 9;
                            $alert_level       = 'danger';
                        } else {
                            if ('CF_AL_06' == $alert_msg_id) {
                                $alert_sort_weight = 9;
                                $alert_level       = 'danger';
                            } else {
                                if ('CF_AL_07' == $alert_msg_id) {
                                    $alert_sort_weight = 7;
                                    $alert_level       = 'danger';
                                } else {
                                    if ('CF_AL_08' == $alert_msg_id) {
                                        $alert_sort_weight = 9;
                                        $alert_level       = 'danger';
                                    } else {
                                        if ('CF_AL_09' == $alert_msg_id) {
                                            $alert_sort_weight = 3;
                                            $alert_level       = 'warning';
                                        } else {
                                            if ('CF_AL_10' == $alert_msg_id) {
                                                $alert_sort_weight = 3;
                                                $alert_level       = 'warning';
                                            } else {
                                                if ('CF_AL_11' == $alert_msg_id) {
                                                    $alert_sort_weight = 3;
                                                    $alert_level       = 'warning';
                                                } else {
                                                    if ('CF_AL_12' == $alert_msg_id) {
                                                        $alert_sort_weight = 3;
                                                        $alert_level       = 'warning';
                                                    } else {
                                                        if ('CF_AL_13' == $alert_msg_id) {
                                                            $alert_sort_weight = 5;
                                                            $alert_level       = 'warning';
                                                        } else {
                                                            if ('CF_AL_14' == $alert_msg_id) {
                                                                $alert_sort_weight = 5;
                                                                $alert_level       = 'warning';
                                                            } else {
                                                                if ('CF_AL_15' == $alert_msg_id) {
                                                                    $alert_sort_weight = 5;
                                                                    $alert_level       = 'warning';
                                                                } else {
                                                                    if ('CF_AL_16' == $alert_msg_id) {
                                                                        $alert_sort_weight = 5;
                                                                        $alert_level       = 'warning';
                                                                    } else {
                                                                        if ('CF_AL_17' == $alert_msg_id) {
                                                                            $alert_sort_weight = 5;
                                                                            $alert_level       = 'warning';
                                                                        } else {
                                                                            if ('CF_AL_18' == $alert_msg_id) {
                                                                                $alert_sort_weight = 5;
                                                                                $alert_level       = 'warning';
                                                                            } else {
                                                                                if ('CF_AL_19' == $alert_msg_id) {
                                                                                    $alert_sort_weight = 5;
                                                                                    $alert_level       = 'warning';
                                                                                } else {
                                                                                    if ('CF_AL_20' == $alert_msg_id) {
                                                                                        $alert_sort_weight = 5;
                                                                                        $alert_level       = 'warning';
                                                                                    } else {
                                                                                        if ('CF_AL_21' == $alert_msg_id) {
                                                                                            $alert_sort_weight = 5;
                                                                                            $alert_level       = 'warning';
                                                                                        } else {
                                                                                            if ('CF_AL_22' == $alert_msg_id) {
                                                                                                $alert_sort_weight = 5;
                                                                                                $alert_level       = 'danger';
                                                                                            } else {
                                                                                                if ('CF_AL_23' == $alert_msg_id) { // HSP_ER + C
                                                                                                    $alert_sort_weight = 7;
                                                                                                    $alert_level       = 'warning';
                                                                                                } else {
                                                                                                    if ('CF_AL_24' == $alert_msg_id) { // HSP_ER + mm/dd
                                                                                                        $alert_sort_weight = 7;
                                                                                                        $alert_level       = 'warning';
                                                                                                    } else {
                                                                                                        if ('CF_AL_25' == $alert_msg_id) { // HSP_HOSP + c
                                                                                                            $alert_sort_weight = 7;
                                                                                                            $alert_level       = 'warning';
                                                                                                        } else {
                                                                                                            if ('CF_AL_26' == $alert_msg_id) { // HSP_HOSP + mm/dd
                                                                                                                $alert_sort_weight = 7;
                                                                                                                $alert_level       = 'warning';
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'alert_sort_weight' => $alert_sort_weight,
            'alert_level'       => $alert_level,
        ];
    }

    /**
     * @param $user_id
     * @param mixed $id
     *
     * @return string
     */
    public function get_alerts_url(
        $user_id,
        $id
    ) {
        $wpBlog     = Practice::where('id', '=', $id)->first();
        $alerts_url = '';
        if ($wpBlog) {
            // $alerts_url = 'https://'. $wpBlog->domain . '/alerts/?user=' . $user_id;
            $alerts_url = 'https://'.$wpBlog->domain.'/manage-patients/'.$user_id.'/summary';
            try {
                $alerts_url = $this->client->get('http://tinyurl.com/api-create.php?url='.$alerts_url)->getBody();
            } catch (ServerException $e) {
                Log::warning("Could not generate tinyurl: {$e->getMessage()}");
            }
        }

        return $alerts_url;
    }

    /**
     * @param $type - the type Adherence, Blood_Pressure, ect
     * @param $items - rules_ucp
     *
     * @return string
     */
    public function process_alert_3_day_missed_items(
        $type,
        $items
    ) {
        $log_string = '';
        if ( ! empty($items)) {
            foreach ($items as $item) {
                // get all active users and loop through them
                $active_users = $this->CI->users_model->get_users_for_active_item($item['items_id'], $this->int_id);
                //echo "<pre>";var_dump($active_users);echo "</pre>";die();
                if ( ! empty($active_users)) {
                    foreach ($active_users as $active_user) {
                        $user_id           = $active_user['user_id'];
                        $item_obs_ids      = '';
                        $observation       = false;
                        $msg_id            = $item['msg_id'];
                        $day1_date         = date('Y-m-d', strtotime('-1 days'));
                        $item_observations = $this->CI->observation_model->get_3day_observations(
                            strtolower($item['alert_key']),
                            $msg_id,
                            $user_id,
                            $day1_date,
                            $this->int_id
                        );
                        //echo "<pre>";var_dump($item_observations);echo "</pre>";
                        if ( ! empty($item_observations)) {
                            $i               = 1; // standard loop counter, 1 = most recent obs found
                            $f               = 0; // the number of found observations (3 should always be found)
                            $dates_processed = [];
                            $dates_processed = [];
                            foreach ($item_observations as $item_obs) {
                                //echo "<pre>";var_dump($item_obs);echo "</pre>";//die();
                                $obs_date = $date = date('Y-m-d', strtotime($item_obs['obs_date']));
                                // prevent more than one per day being processed (desc so most recent gets first)
                                if (in_array($obs_date, $dates_processed)) {
                                    continue 1;
                                }
                                $dates_processed[] = $obs_date;

                                // add NR if blank
                                if ('' == $item_obs['obs_value']) {
                                    $item_obs['obs_value'] = 'NR';
                                }
                                if ($i <= 3) {
                                    // if not already alert, and is blank or "N", tally towards alert
                                    if (empty($item_obs['dm_log_missed_'.strtolower($item['alert_key'])]) && ('' == $item_obs['obs_value'] || 'n' == strtolower($item_obs['obs_value']) || 'nr' == strtolower($item_obs['obs_value']))) {
                                        //$log_string .= "Patient[{$user_id}][{$msg_id}][{$item_obs['obs_date']}] missed med, obs_value=" .$item_obs['obs_value'] . PHP_EOL;
                                        $send_alert = true;
                                        $item_obs_ids .= '('.$item_obs['obs_id']."[{$item_obs['obs_date']}][{$item_obs['obs_value']}])";
                                        ++$f;
                                        if (1 == $i) {
                                            $observation = $item_obs; // this will store the first obs
                                        }
                                    } else {
                                        if ( ! empty($item_obs['dm_log_missed_'.strtolower($item['alert_key'])])) {
                                            $item_obs['obs_value'] = 'ALREADY ALERTED';
                                        }
                                        $item_obs_ids .= '('.$item_obs['obs_id']."[{$item_obs['obs_date']}][{$item_obs['obs_value']}])";
                                    }
                                    ++$i;
                                }
                            }
                        }
                        if ( ! empty($send_alert) && (3 == $f) && $observation) {
                            // fire alert on first day obs
                            $label                   = 'danger';
                            $extra_vars['alert_key'] = $item['alert_key'];
                            $message_id              = $item['alert_msg_id']; // missed meds are CF_AL_13 through CF-AL-21
                            if ( ! $item['alert_msg_id']) {
                                $message_id = '';
                            }
                            $send_email = false;
                            $send_alert = "Patient[{$user_id}][{$msg_id}][ALERT] missed 3 days in a row, checked obs[{$item_obs_ids}]".PHP_EOL;
                            $log_string .= $send_alert;
                            // add obsmeta dm_log_missed_' . strtolower($item['alert_key'])
                            $observationmeta_paramaters = [
                                'obs_id'     => $observation['id'],
                                'comment_id' => $observation['comment_id'],
                                'message_id' => $message_id,
                                'meta_key'   => 'dm_log_missed_'.strtolower($item['alert_key']),
                                'meta_value' => $send_alert,
                            ];
                            $observationmeta_id = $this->CI->observationmeta_model->insert_observationmeta(
                                $observationmeta_paramaters,
                                $this->int_id
                            );
                            $log_string .= 'added new observationmeta dm_log_missed_'.strtolower($item['alert_key'])." - obsmeta_id = {$observationmeta_id}".PHP_EOL;
                            if ('Adherence' == $type) { // HACK FIX
                                // update observation to ensure it has obs_key
                                $observation_paramaters = [
                                    'obs_id'  => $observation['id'],
                                    'obs_key' => 'Adherence',
                                ];
                                $this->CI->observation_model->update_observation(
                                    $observation_paramaters,
                                    $this->int_id
                                );
                            }
                            // send actual alert
                            $log_string .= $this->send_obs_alert(
                                $observation,
                                $message_id,
                                $send_email,
                                $extra_vars,
                                false,
                                $this->int_id
                            );
                        } else {
                            $log_string .= "Patient[{$user_id}][{$msg_id}][GOOD] did not miss for past 3 days asked, checked obs[{$item_obs_ids}]".PHP_EOL;
                        }
                    }
                }
            }
        }
        $this->time_end = microtime(true);
        $log_string .= PHP_EOL.PHP_EOL.'process_alert_3_day_missed_items() Excecution Time: '.($this->time_end - $this->time_start).' (seconds)'.PHP_EOL;

        return $log_string;
    }

    // END process_alert_obs METHOD GROUP

    /**
     * @param $user
     * @param $userUcpData
     * @param $observation
     * @param $int_id
     *
     * @return array|bool
     */
    public function process_alert_obs_a1c(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value) && 0 != $obs_value) {
            return false;
        }
        //An A1C level below this percent is considered normal
        $normalLevel = 5.7;

        //An A1C level higher than this indicates diabetes
        $diabetesLevel = 7.1;

        if ($obs_value > $diabetesLevel) {
            $label = 'danger';
        } elseif ($obs_value >= $normalLevel && $obs_value <= $diabetesLevel) {
            $label = 'warning';
        } else {
            $label = 'success';
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_adherence(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $obs_value  = $observation['obs_value'];
        $log_string = "OBSERVATION[{$observation['id']}]  Patient[{$observation['user_id']}] obs_value = ".$obs_value.PHP_EOL;

        // start
        if ('Y' == strtoupper($obs_value) || 'YES' == strtoupper($obs_value)) {
            $label = 'success';
        } else {
            if ('N' == strtoupper($obs_value) || 'NO' == strtoupper($obs_value)) {
                $label = 'danger';
            } else {
                // this is where we can pick up missed meds, if the obs is NR and from previous day we can close it out here
                $obs_date = date_create($observation['obs_date']);
                if (($obs_date->format('Y-m-d')) < date('Y-m-d')) {
                    // date is prior so we can close it out
                    $log_string .= ' Non Response from over 1 day ago'.PHP_EOL;
                    $label = 'danger';
                }
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_blood_pressure(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value)) {
            return false;
        }
        $extra_vars['bpvalue'] = $obs_value;
        // extract systolic from systolic/diastolic formatted value
        $pieces = explode('/', $obs_value);
        if (2 == sizeof($pieces)) {
            $obs_value = $pieces[0];
        }
        if ( ! isset($userUcpData['alert_keys']['bloodPressure'])) {
            $log_string .= 'Missing UCP data for bp and/or bp low';
            $label = 'success';
        } else {
            $bloodPressure = $userUcpData['alert_keys']['bloodPressure'];

            //the CLH healthy range
            $min_systolic_bp_healthy_range = 100;
            $max_systolic_bp_healthy_range = 140;

            $lowAlert = empty($bloodPressure)
                ? 80
                : $bloodPressure->systolic_low_alert;
            $highAlert = empty($bloodPressure)
                ? 180
                : $bloodPressure->systolic_high_alert;

            $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] BP High: {$max_systolic_bp_healthy_range}(systolic),  BP Low: {$min_systolic_bp_healthy_range}(systolic) - obs_value={$obs_value}(systolic)".PHP_EOL;
            // compare observation value (systolic/diastolic) to patient max/min blood pressure limit
            if ( ! empty($obs_value) && ! empty($min_systolic_bp_healthy_range) && ! empty($max_systolic_bp_healthy_range)) {
                if ($obs_value >= $highAlert || $obs_value <= $lowAlert) { //81
                    $message_id = 'CF_AL_02';
                    $send_alert = "[{$obs_value} (systolic) is <= {$min_systolic_bp_healthy_range} (systolic)]";
                    $log_string .= $send_alert;
                    $send_email = true;
                    $label      = 'danger';
                } else {
                    if ($obs_value >= $min_systolic_bp_healthy_range && $obs_value <= $max_systolic_bp_healthy_range) {
                        $label = 'success';
                    } else {
                        if (($obs_value <= $min_systolic_bp_healthy_range && $obs_value >= $lowAlert) || ($obs_value <= $highAlert && $obs_value >= $max_systolic_bp_healthy_range)) {
                            $label = 'warning';
                        }
                    }
                }
            } else {
                $log_string .= 'Missing UCP data for bp and/or bp low';
                $label = 'success';
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_blood_sugar(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value)) {
            return false;
        }
        //dd($userUcpData);
        //blood-sugar-bs-high-alert
        //blood-sugar-bs-low-alert
        if ( ! isset($userUcpData['alert_keys']['bloodSugar'])) {
            $log_string .= 'Missing UCP data for bs and/or bs low';
            $label = 'success';
        } else {
            $bloodSugar = $userUcpData['alert_keys']['bloodSugar'];

            $lowAlert = empty($bloodSugar)
                ? 60
                : $bloodSugar->low_alert;
            $highAlert = empty($bloodSugar)
                ? 350
                : $bloodSugar->high_alert;

            $max_blood_sugar_healthy_range = 140;
            $min_blood_sugar_healthy_range = 80;

            $extra_vars['bsvalue'] = $obs_value;
            $log_string            = PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] BS High: {$max_blood_sugar_healthy_range}, BS Low: {$lowAlert}".PHP_EOL;

            if ( ! empty($obs_value)) {
                if (($obs_value <= $lowAlert) || ($obs_value >= $highAlert)) { //61
                    $message_id = 'CF_AL_04';
                    $send_alert = "{$obs_value} (systolic) is <= {$lowAlert} (systolic)";
                    $send_email = true;
                    $label      = 'danger';
                } else {
                    if ($obs_value >= $min_blood_sugar_healthy_range && $obs_value <= $max_blood_sugar_healthy_range) {
                        $label = 'success';
                    } else {
                        if (($obs_value <= $min_blood_sugar_healthy_range && $obs_value >= $lowAlert) || ($obs_value <= $highAlert && $obs_value >= $max_blood_sugar_healthy_range)) { //351
                            $label = 'warning';
                        }
                    }
                }
            } else {
                $log_string .= 'Missing UCP data for bs and/or bs low';
                $label = 'success';
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_call(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = 'danger';
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value  = $observation['obs_value'];
        $message_id = 'CF_AL_01';
        $send_alert = 'Patient requested a call'.PHP_EOL;
        $log_string = "OBSERVATION[{$observation['id']}]  Patient[{$observation['user_id']}] requested a CALL".PHP_EOL;
        $send_email = false;

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_cigarettes(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value) && 0 != $obs_value) {
            return false;
        }
        $max_cigs = 4;

        if ($obs_value >= $max_cigs) {
            $label      = 'danger';
            $message_id = 'CF_AL_07';
            $send_alert = "The patient's cigarette number is too high. The patient had {$obs_value} cigarettes. An alert is sent out if the cigarette count is {$max_cigs} or more.";
            $log_string = "OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}][ucp cigs={$max_cigs}] cigs too high, {$obs_value} > {$max_cigs}".PHP_EOL;
            $send_email = false;
        } else {
            $log_string = "OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}][ucp cigs={$max_cigs}] cigs lower than ucp max, {$obs_value} > {$max_cigs}".PHP_EOL;
            $label      = 'success';
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_hsp(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = 'warning';
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value) && 0 != $obs_value) {
            return false;
        }
        if ('b' == strtolower($obs_value)) {
            return false;
        }
        $obs_date = new DateTime($observation['obs_date']);
        //echo $obs_date->format('m/d');

        //$log_string = "OBSERVATION[{$observation['id']}] obs_value = " . $obs_value . PHP_EOL;
        $log_string = '';
        if ('HSP_ER' == $observation->obs_key) {
            if ('c' == strtolower($obs_value)) {
                $log_string .= 'Patient in the ER as of '.$obs_date->format('m/d').', follow up required';
                $message_id = 'CF_AL_23'; // HSP_ER + C
                $send_alert = '';
            } else {
                $log_string .= 'Visited ER on '.str_replace('_', '/', $obs_value).', follow up required';
                $message_id = 'CF_AL_24'; // HSP_ER + dd_mm
                $send_alert = '';
            }
        } else {
            if ('HSP_HOSP' == $observation->obs_key) {
                if ('c' == strtolower($obs_value)) {
                    $log_string .= 'Patient in the Hospital as of '.$obs_date->format('m/d').', follow up required';
                    $message_id = 'CF_AL_25'; // HSP_HOSP + C
                    $send_alert = '';
                } else {
                    $log_string .= 'Hospital Discharge on '.str_replace(
                        '_',
                        '/',
                        $obs_value
                    ).', follow up required';
                    $message_id = 'CF_AL_26'; // HSP_HOSP + dd_mm
                    $send_alert = '';
                }
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];

        // insert activity
        /*
        $activity_params = array(
            'type' => 'HospitalVisit',
            'duration' => '0',
            'duration_unit' => 'minutes',
            'patient_id' => $observation['user_id'],
            'provider_id' => '0',
            'logger_id' => $observation['user_id'],
            'comment_id' => '0',
            'sequence_id' => '0',
            'obs_message_id' => '',
            'logged_from' => 'dm',
            'performed_at' => $observation['obs_date'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->CI->db->insert('lv_activities', $activity_params);
        */

        //var_dump($result_array);die();
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_other(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $obs_value  = $observation['obs_value'][0];
        if (empty($obs_value) || ('Y' != strtoupper($obs_value) && 'N' != strtoupper($obs_value))) {
            return false;
        }
        $log_string = "OBSERVATION[{$observation['id']}] obs_value = ".$obs_value.PHP_EOL;

        // start
        if ('Y' == strtoupper($obs_value) || 'YES' == strtoupper($obs_value)) {
            $label = 'success';
        } else {
            if ('N' == strtoupper($obs_value) || 'NO' == strtoupper($obs_value)) {
                $label = 'danger';
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_severity(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value)) {
            return false;
        }
        $max_severity = 7;
        if ($obs_value < 4) {
            $label = 'success';
            $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] Severity: {$obs_value} < 4".PHP_EOL;
        } else {
            if ($obs_value > 3 && $obs_value < 7) {
                $label = 'warning';
                $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] Severity: {$obs_value} > 3 && < 7".PHP_EOL;
            } else {
                if ($obs_value > 6) {
                    $label = 'danger';
                }
            }
        }
        // here is a hack to get symptoms text, first get parent observation (menu)
        /*
        $parent_observation_info = $this->CI->observation_model->get_parent_symptom_observation($observation['comment_id'], ((int)$observation['sequence_id']-1), $this->int_id);
        if($parent_observation_info) {
            // get meta_key for parent menu, rpt_sum_resp_txt-{#}
            $itemmeta_result = $this->CI->rules_model->get_itemmeta_value_by_key($parent_observation_info->items_id, 'rpt_sum_resp_txt-' . (int)$parent_observation_info->obs_value, $this->int_id);
            if(isset($itemmeta_result->meta_value)){
                $extra_vars['symptom'] = $itemmeta_result->meta_value;
            }
        }
        */
        // get symptom label for extra_vars
        $question              = CPRulesQuestions::where('msg_id', '=', $observation['obs_message_id'])->first();
        $extra_vars['symptom'] = '-';
        if ($question) {
            $extra_vars['symptom'] = $question->description;
        }
        if ((false !== $obs_value) && $obs_value >= $max_severity) {
            $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] Severity: {$obs_value}".PHP_EOL;
            $send_alert           = "{$obs_value} is >= {$max_severity}";
            $send_email           = true;
            $message_id           = 'CF_AL_08';
            $extra_vars['symsev'] = $obs_value;
            $label                = 'danger';
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    /**
     * @param $userUcpData
     * @param $observation
     * @param mixed $user
     * @param mixed $int_id
     *
     * @return array
     */
    public function process_alert_obs_weight(
        $user,
        $userUcpData,
        $observation,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        // defaults
        $label      = false;
        $extra_vars = [];
        $message_id = '';
        $send_alert = false;
        $send_email = false;
        $log_string = '';

        // start
        $obs_value = $observation['obs_value'];
        if (empty($obs_value)) {
            return false;
        }
        // WEIGHT PREVIOUS MATCH COMPARISON ALERT
        if ($userUcpData['obs_keys']['Weight_CHF']) {
            // get previous weight observation
            $prev_obs = $user->observations()
                ->whereRaw("obs_date < DATE_FORMAT('{$observation['obs_date']}', '%Y-%m-%d')")
                ->where('id', '<', $observation['id'])
                ->where('obs_key', '=', $observation['obs_key'])
                ->where('obs_unit', '!=', 'invalid')
                ->where('obs_unit', '!=', 'scheduled')
                ->orderBy('obs_date', 'desc')
                ->first();
            if ( ! empty($prev_obs)) {
                // calculate dates
                $dateLast  = new DateTime($prev_obs->obs_date);
                $dateNow   = new DateTime($observation['obs_date']);
                $intDiff   = date_diff($dateLast, $dateNow);
                $intWtDiff = $obs_value - $prev_obs->obs_value;

                $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}({$observation['obs_date']})] Weight: {$obs_value}lbs CHF CHECK:: PREV OBSERVATION[{$prev_obs->obs_id}({$prev_obs->obs_date})][{$intDiff->days} days prior] Prev weight:{$prev_obs->obs_value}lbs".PHP_EOL;

                switch ($intDiff->format('%a')) {
                    case 0:
                    case 1:
                        if ($intWtDiff > 2) {
                            $send_alert              = "{$obs_value}lbs is > 2lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
                            $message_id              = 'CF_AL_06';
                            $send_email              = true;
                            $extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
                            $extra_vars['chfperiod'] = '1 days';
                            $label                   = 'danger';
                        } else {
                            $label = 'success';
                            $log_string .= PHP_EOL."Weight is good, {$prev_obs->obs_value} is < 2 lbs difference".PHP_EOL;
                        }
                        break;
                    case 2:
                        if ($intWtDiff > 4) {
                            $send_alert              = "{$obs_value}lbs is > 4lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
                            $message_id              = 'CF_AL_06';
                            $send_email              = true;
                            $extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
                            $extra_vars['chfperiod'] = '2 days';
                            $label                   = 'danger';
                        } else {
                            $label = 'success';
                            $log_string .= PHP_EOL."Weight is good, {$prev_obs->obs_value} is < 4 lbs difference".PHP_EOL;
                        }
                        break;
                    case 3:
                    case 4:
                    case 5:
                    case 6:
                    case 7:
                        if ($intWtDiff > 5) {
                            $send_alert              = "{$obs_value}lbs is > 4lbs greater than prior observation[{$prev_obs->obs_value}]lbs {$intDiff->days} day prior";
                            $message_id              = 'CF_AL_06';
                            $send_email              = true;
                            $extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
                            $extra_vars['chfperiod'] = "{$intDiff->days} days";
                            $label                   = 'danger';
                        } else {
                            $label = 'success';
                            $log_string .= PHP_EOL."Weight is good, {$prev_obs->obs_value} is < 5 lbs difference".PHP_EOL;
                        }
                        break;
                        break;
                    default:
                        $send_alert              = "Patient hasnt reported weight in {$intDiff->days} days";
                        $message_id              = 'CF_AL_22';
                        $send_email              = true;
                        $extra_vars['chfpounds'] = ($obs_value - $prev_obs->obs_value);
                        $extra_vars['chfperiod'] = "{$intDiff->days} days";
                        $label                   = 'danger';
                        break;
                }
            } else {
                $label = 'success';
                $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] Patient[{$observation['user_id']}] Missing required previous obs for chf check".PHP_EOL;
            }
        } else {
            $label = 'success';
            $log_string .= PHP_EOL.'user does not have chf checked for monitoring, checking weight'.PHP_EOL;
            // WEIGHT TARGET ALERT
            $max_cigs = 4;
            if ( ! isset($userUcpData['alert_keys']['Weight'])) {
                $log_string .= PHP_EOL.'user does not have a target weight set, cannot check'.PHP_EOL;
                $label = 'success';
            } else {
                //dd($userUcpData);
                $max_weight = $userUcpData['alert_keys']['Weight'];
                $obs_value  = $obs_value;
                if ((false !== $obs_value)) {
                    $log_string .= PHP_EOL."OBSERVATION[{$observation['id']}] User {$observation['user_id']} Weight: {$obs_value}".PHP_EOL;
                    $label = 'success';
                    if ($max_weight) {
                        if (($obs_value / $max_weight) > 1.15) {
                            $log_string .= PHP_EOL." {$obs_value} / {$max_weight} > 1.15 = danger ".PHP_EOL;
                            $label = 'danger';
                        } else {
                            if (($obs_value / $max_weight) >= 1.06) {
                                $log_string .= PHP_EOL." {$obs_value} / {$max_weight} > 1.06 && <= 1.15 = warning ".PHP_EOL;
                                $label = 'warning';
                            } else {
                                if (($obs_value / $max_weight) > 1) {
                                    $log_string .= PHP_EOL." {$obs_value} / {$max_weight} > 0 && <= 1.06 = success ".PHP_EOL;
                                    $label = 'success';
                                } else {
                                    $log_string .= PHP_EOL.' Hit no range, should be success'.PHP_EOL;
                                    $label = 'success';
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'log_string' => $log_string,
            'message_id' => $message_id,
            'send_alert' => $send_alert,
            'send_email' => $send_email,
            'extra_vars' => $extra_vars,
            'label'      => $label,
        ];
    }

    // START process_alert_obs METHOD GROUP

    /**
     * @param $email_message
     * @param $extra_vars
     *
     * @return mixed
     */
    public function process_message_substitutions(
        $email_message,
        $extra_vars
    ) {
        if ( ! empty($extra_vars)) {
            foreach ($extra_vars as $substitute => $value) {
                if (false !== strpos($email_message, '#'.$substitute.'#')) {
                    $email_message = str_replace('#'.$substitute.'#', $value, $email_message);
                }
            }
        }

        return $email_message;
    }

    /**
     * Summary: run_process_obs_alerts - search for new observations and process them, checking for alerts.
     *
     * @param $obs_id
     *
     * @return string
     */
    public function process_obs_alerts($obs_id)
    {
        $this->time_start = microtime(true);
        // set blog id
        //$this->int_id = $int_id;

        // start logging
        $log_string = PHP_EOL."---process_obs_alerts({$obs_id}) START".PHP_EOL;

        // first get any new [alert_key or obs_id] observations that haven't been processed
        //$observations = $this->CI->observation_model->get_dm_observations($observation->obs_key, $obs_id, $this->int_id);
        $observation = Observation::find($obs_id);
        if ( ! $observation) {
            return false;
        }

        // skip others (hack, bad planning)
        if ('Other' == $observation->obs_key && ( ! in_array($observation['obs_value'], [
            'y',
            'Y',
            'n',
            'N',
        ]))
        ) {
            //continue 1;
        }
        $message_id = 'n/a';
        $extra_vars = []; // extra_vars get stored for later email/sms/msg substitution
        $log_string .= "Checking ({$observation->obs_key}) observation[{$observation['id']}][{$observation['obs_date']}][{$observation['obs_value']}]".PHP_EOL;
        // get user data for observation
        $user = User::find($observation->user_id);

        $weight        = $user->cpmWeight()->first();
        $bloodSugar    = $user->cpmBloodSugar()->first();
        $bloodPressure = $user->cpmBloodPressure()->first();
        $smoking       = $user->cpmSmoking()->first();

        $userUcpData['obs_keys'] = [
            'Other' => '',
            //empty
            'Adherence' => '',
            //empty
            'Cigarettes' => '',
            //empty
            'Weight' => '',
            //empty
            'Weight_CHF' => empty($weight)
                ?: $weight->monitor_changes_for_chf,
            //bool
            'Blood_Sugar' => '',
            //empty
            'Blood_Pressure' => '',
            //empty
            'A1c' => '',
            //empty
            'HSP' => '',
            //empty
        ];
        $userUcpData['alert_keys'] = [
            'Weight' => empty($weight)
                ? false
                : $weight->target,
            'bloodSugar' => empty($bloodSugar)
                ? false
                : $bloodSugar,
            'bloodPressure' => empty($bloodPressure)
                ? false
                : $bloodPressure,
        ];

        $first_name                = $user->first_name;
        $last_name                 = $user->last_name;
        $extra_vars['patientname'] = $first_name.' '.$last_name;
        $extra_vars['alerts_url']  = $this->get_alerts_url($observation['user_id'], $user->program_id);
        $extra_vars['alert_key']   = str_replace('_', ' ', $observation->obs_key);
        //$user_data_ucp = $user_data[$observation['user_id']]['usermeta']['user_care_plan'];
        $obs_value  = $observation['obs_value'];
        $result     = false;
        $send_alert = false; // this will become a message string if an alert is found
        $send_email = false;
        // if users ucp has a value for the given alert key, compare and check if alert should be triggered
        switch ($observation->obs_key) {
            // Blood Pressure
            case 'Blood_Pressure':
                $result = $this->process_alert_obs_blood_pressure($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Blood Sugar
            case 'Blood_Sugar':
                $result = $this->process_alert_obs_blood_sugar($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Weight
            case 'Weight':
                $result = $this->process_alert_obs_weight($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Weekly cigarettes smoked
            case 'Cigarettes':
                $result = $this->process_alert_obs_cigarettes($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            case 'A1c':
                $result = $this->process_alert_obs_a1c($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Call requested
            case 'Call':
                $result = $this->process_alert_obs_call($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Symptoms
            case 'Severity':
                $result = $this->process_alert_obs_severity($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Adherence
            case 'Adherence':
                $result = $this->process_alert_obs_adherence($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // Other
            case 'Other':
                $result = $this->process_alert_obs_other($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
            // HSP
            case 'HSP_ER':
            case 'HSP_HOSP':
                $result = $this->process_alert_obs_hsp($user, $userUcpData, $observation, $this->int_id);
                if ($result) {
                    $log_string .= $result['log_string'];
                    $message_id = $result['message_id'];
                    $send_alert = $result['send_alert'];
                    $send_email = $result['send_email'];
                    $extra_vars = array_merge($extra_vars, $result['extra_vars']);
                }
                break;
        }

        if (empty($result['label'])) {
            $log_string .= "{$observation->id} no label set, not adding any obsmeta ".PHP_EOL;
        } else {
            // insert observationmeta for dm result
            $observationMeta             = new ObservationMeta();
            $observationMeta->obs_id     = $observation->id;
            $observationMeta->comment_id = $observation->comment_id;
            $observationMeta->message_id = $message_id;
            $observationMeta->meta_key   = 'dm_alert_level';
            $observationMeta->meta_value = $result['label'];
            $observationMeta->save();
            $log_string .= "added new observationmeta dm_alert_level - obsmeta_id = {$observation->id}".PHP_EOL;

            $observationMeta             = new ObservationMeta();
            $observationMeta->obs_id     = $observation->id;
            $observationMeta->comment_id = $observation->comment_id;
            $observationMeta->message_id = $message_id;
            $observationMeta->meta_key   = 'dm_log';
            $observationMeta->meta_value = $result['log_string'].$result['send_alert'];
            $observationMeta->save();
            $log_string .= "added new observationmeta dm_log - obsmeta_id = {$observation->id}".PHP_EOL;

            if (false !== $send_alert) {
                $log_string .= "SEND ALERT [{$send_alert}]".PHP_EOL;
                // if exception, trigger alert flow
                $log_string .= $this->send_obs_alert(
                    $observation,
                    $message_id,
                    $send_email,
                    $extra_vars,
                    $observation->obs_method,
                    $this->int_id
                );
            }
        }

        // end logging and return
        $log_string .= PHP_EOL."---process_obs_alerts({$observation->obs_key}) END".PHP_EOL;
        $this->time_end = microtime(true);
        $log_string .= PHP_EOL."{$observation->obs_key} Excecution Time: ".($this->time_end - $this->time_start).' (seconds)'.PHP_EOL;

        return $log_string;
    }

    /**
     * Summary: run_process_3_day_missed_biometrics - search for active patients who missed
     * biometric 3 days in a row, and if so trigger alert.
     *
     * @param $int_id
     * @param mixed $alert_key
     *
     * @return string
     */
    public function run_process_3_day_missed_biometrics(
        $alert_key,
        $int_id
    ) {
        $this->time_start = microtime(true);
        // set blog id
        $this->int_id = $int_id;

        $log_string = '';
        // get message ids
        $items = $this->CI->rules_model->get_items_by_alert_key($alert_key, $this->int_id);

        // harcode in alert message ids
        $items_processed = [];
        foreach ($items as $item) {
            switch ($alert_key) {
                case 'Blood_Pressure':
                    $item['alert_msg_id'] = 'CF_AL_09';
                    break;
                case 'Blood_Sugar':
                    $item['alert_msg_id'] = 'CF_AL_10';
                    break;
                case 'Weight':
                    $item['alert_msg_id'] = 'CF_AL_11';
                    break;
                case 'Cigarettes':
                    $item['alert_msg_id'] = 'CF_AL_12';
                    break;
                default:
                    break;
            }
            $items_processed[] = $item;
        }
        //echo "<pre>";var_dump($items_processed);echo "</pre>";//die();

        // process
        $log_string .= $this->process_alert_3_day_missed_items($alert_key, $items_processed);

        return $log_string;
    }

    /**
     * Summary: run_process_3_day_missed_meds - search for active patients who missed
     * med 3 days in a row, and if so trigger alert.
     *
     * @param $int_id
     * @param mixed $alert_key
     *
     * @return string
     */
    public function run_process_3_day_missed_observations(
        $alert_key,
        $int_id
    ) {
        $this->time_start = microtime(true);
        // set blog id
        $this->int_id = $int_id;

        $log_string = '';
        // get message ids
        $adherence_items = $this->CI->rules_model->get_adherence_items($this->int_id);
        //process
        $log_string .= $this->process_alert_3_day_missed_items('Adherence', $adherence_items);

        return $log_string;
    }

    /**
     * Summary: send_obs_alert gets passed an observation that was found to trigger an alert, and processes it.
     *
     * @param $observation
     * @param $message_id
     * @param mixed $send_email
     * @param mixed $extra_vars
     * @param mixed $source
     * @param mixed $int_id
     *
     * @return string
     */
    public function send_obs_alert(
        $observation,
        $message_id,
        $send_email,
        $extra_vars,
        $source = false,
        $int_id
    ) {
        // set blog id
        $this->int_id = $int_id;

        $log_string = PHP_EOL;

        // alert_sort_weight / alert_level
        $alert_sort_weight = 0;
        $alert_level       = 'success';
        $msg_info          = $this->get_alert_msg_info($message_id);
        if ( ! empty($msg_info)) {
            $alert_sort_weight = $msg_info['alert_sort_weight'];
            $alert_level       = $msg_info['alert_level'];
        }

        $status = 'PA';
        if ( ! $send_email) {
            $status = 'NR';
            // override send email, set to NR if source = in-office visit
            if ($source) {
                if ('ov_reading' == strtolower($source)) {
                    $status = 'RV';
                    $log_string .= 'source = ov_reading, status = RV'.PHP_EOL;
                }
            }
        } else {
            // override send email, set to NR if source = in-office visit
            if ($source) {
                if ('ov_reading' == strtolower($source)) {
                    $send_email = false;
                    $status     = 'RV';
                    $log_string .= 'source = ov_reading, dont send email, status = RV'.PHP_EOL;
                }
            }
            // override send email, set to NR if > 1day old
            if (isset($observation['obs_date'])) {
                if (strtotime($observation['obs_date']) < strtotime('-1 days')) {
                    $send_email = false;
                    $status     = 'NR';
                    $log_string .= 'obs_date > 1 day old, dont send email, status = NR'.PHP_EOL;
                }
            }
        }

        $serial_content = serialize([
            'status'      => $status,
            'alert_level' => $alert_level,
            'obsid'       => $observation['id'],
            'comment_id'  => $observation['obs_comment_id'],
            'message_id'  => $message_id,
            'user'        => $observation['user_id'],
            'modifier'    => 'dmon',
            'blogid'      => $this->int_id,
            'date_time'   => date('Y-m-d H:i:s'),
        ]);

        // insert observationmeta
        $observationMeta             = new ObservationMeta();
        $observationMeta->obs_id     = $observation->id;
        $observationMeta->comment_id = $observation->comment_id;
        $observationMeta->message_id = $message_id;
        $observationMeta->meta_key   = 'alert_status_hist';
        $observationMeta->meta_value = $serial_content;
        $observationMeta->save();
        $log_string .= "added new observationmeta alert_status_hist - obsmeta_id = {$observationMeta->id}".PHP_EOL;

        $observationMeta             = new ObservationMeta();
        $observationMeta->obs_id     = $observation->id;
        $observationMeta->comment_id = $observation->comment_id;
        $observationMeta->message_id = $message_id;
        $observationMeta->meta_key   = 'alert_status_change';
        $observationMeta->meta_value = $serial_content;
        $observationMeta->save();
        $log_string .= "added new observationmeta alert_status_change - obsmeta_id = {$observationMeta->id}".PHP_EOL;

        $observationMeta             = new ObservationMeta();
        $observationMeta->obs_id     = $observation->id;
        $observationMeta->comment_id = $observation->comment_id;
        $observationMeta->message_id = $message_id;
        $observationMeta->meta_key   = 'alert_sort_weight';
        $observationMeta->meta_value = $alert_sort_weight;
        $observationMeta->save();
        $log_string .= "added new observationmeta alert_sort_weight - obsmeta_id = {$observationMeta->id}".PHP_EOL;

        $observationMeta             = new ObservationMeta();
        $observationMeta->obs_id     = $observation->id;
        $observationMeta->comment_id = $observation->comment_id;
        $observationMeta->message_id = $message_id;
        $observationMeta->meta_key   = 'send_obs_alert_log';
        $observationMeta->meta_value = $log_string;
        $observationMeta->save();
        $log_string .= "added new observationmeta send_obs_alert_log - obsmeta_id = {$observationMeta->id}".PHP_EOL;

        return $log_string;
    }
}
