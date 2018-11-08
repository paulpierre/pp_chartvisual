<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** ===================
 *  alert.controller.php
 *  ===================

 *
 */


/**
 *  First lets see if anything is out of sync
 */


/** ===================================
 *  http://api.chartvisual/alert/rs
 *  ===================================
 *  Cron check to see if we've any dispatches to send to subscribers

 *  CURL Post
 * curl --data "indicator=rs&pair=gbpusd&time=h1&param=2" http://api.chartvisual/alert/check
 */

$indicator = $controllerFunction;
if(!isset($indicator)) exit('indicator not set!');

$pairs = Array(
    'eurusd',
    'usdjpy',
    'gbpusd',
    'audusd',
    'usdcad',
    'usdchf',
    'nzdusd',
    'gbpjpy',
    'eurjpy',
    'eurgbp',
    'eurchf',
    'nzdusd'
);


$indicators = Array(
    'tl',
    'cs',
    'rs');

if(!in_array($indicator,$indicators)) exit('not a valid indicator!');

$time_frames = Array(
    'm5',
    'm15',
    'm30',
    'h1',
    'h4',
    'd1',
    'w1',
    'mn1'
);

$time_range = Array(
    'm5' =>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 1 HOUR)',
    'm15'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 1 HOUR)',
    'm30'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 3 HOUR)',
    'h1'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 5 HOUR)',
    'h4'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 12 HOUR)',
    'd1'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 1.5 DAY)',
    'w1'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 1.5 WEEK)',
    'mn1'=>'AND time > DATE_SUB(CONVERT_TZ(NOW(),\'+00:00\',\'+03:00\'), INTERVAL 1.5 MONTH)'
);//AND time > DATE_SUB(NOW(), INTERVAL 2 DAY)


$db = new Database();

/** =========================
 *  CHECK EVERY CURRENCY PAIR
 *  =========================
 */

foreach($pairs as $pair)
    {
        $currency_pair = strtoupper($pair);

        /** ============================================
         *  CHECK EVERY TIMEFRAME FOR EACH CURRENCY PAIR
         *  ============================================
         */

        foreach($time_frames as $tf)
        {
            $time_frame = strtoupper($tf);

            /** ================================
             *  LETS CHECK FOR BUY OR SELL SCALP
             *  ================================
             */



            $alert_param = 1;

            $q = 'SELECT buy, sell, bg, time FROM ' . $currency_pair . ' WHERE ((buy=bg AND buy !=0 AND bg !=0 AND tf="' . $time_frame . '") OR (sell=bg AND sell !=0 AND bg !=0 AND tf="' . $time_frame . '")) AND tf !="M1" ' . $time_range[$tf] . ' LIMIT 1';

            /**
             * SELECT buy, sell, bg, time FROM EURCHF WHERE ((buy=bg AND buy !=0 AND bg !=0 AND tf="m5") OR (sell=bg AND buy !=0 AND bg !=0 AND tf="M5")) AND tf !="M1"  AND time > DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1
             */

            $_result = $db->db_query($q,'topforex_scalper');

            //If there are no results, lets skip hits time frame
            if(empty($_result)) continue;

            //lets set the alert param as BUY=1 or SELL=2
            if($_result[0]['buy'] == $_result[0]['bg']) $alert_param = 1;
                else $alert_param = 2;

            $indicator_time = $_result[0]['time'];

            error_log('new alert found: ' . print_r($_result,true));

            /** =========================================================
             *  CHECK TO SEE IF THIS ALERT HAS BEEN SENT TO USERS ALREADY
             *  =========================================================
             */
            $q = 'SELECT * FROM alert_sent WHERE indicator="' . $indicator .'" AND time="' . $indicator_time . '" AND tf="'.$time_frame .'" AND alert_param=' . $alert_param . ' AND alert_currency_pair="'. $currency_pair.'"';
            $result = $db->db_query($q,DATABASE_USER);

            error_log('checking if alert is in DB: ' . print_r($result,true));


            if(empty($result))
            {

                /**
                 *  IF NO RESULTS, IT MEANS IT IS A NEW ALERT, SO LETS INITIATE A DISPATCH
                 */
                /**
                 *  NOW THAT THE EMAIL IS SENT, LETS WRITE alert_sent
                 */

                $data = Array(
                    'indicator'=>$indicator,
                    'pair'=>$currency_pair,
                    'tf'=>$time_frame,
                    'param'=>$alert_param,
                    'time'=>$indicator_time
                );
                if(alert_dispatch($data))
                {
                    //error_log(count($email_list) . ' emails successfully sent');

                    $db_columns = Array(
                        'indicator'=>$indicator,
                        'alert_param'=>$alert_param,
                        'tf'=>$time_frame,
                        'alert_currency_pair'=>$currency_pair,
                        'time'=>$indicator_time,
                        'tcreate'=>current_timestamp()
                    );

                    $result = $db->db_create('alert_sent',$db_columns,DATABASE_USER);
                    if(!$result)
                    {
                        error_log('error writing to alert_sent');
                        continue;
                    } else {

                    }
                }
                else {
                    error_log('dispatch failed for: ' . print_r($data,true));
                }
            }
             else {
                 error_log('dispatch has already been sent');
             }
        }
    }



function alert_dispatch($data)
{
    /** ==============
     *  alert_dispatch
     *  ==============
     * Dispatches notifications to users for indicators
     *
     */

    $db = new Database();

    $indicator = strtolower($data['indicator']);
    $currency_pair = strtolower($data['pair']);
    $time_frame = strtolower($data['tf']);
    $param = strtolower($data['param']);
    $datetime = $data['time'];




    //list of indicators supported
    global $indicators;

    //list of timeframes supported
    global $time_frames;
    global $pairs;

    if(!in_array(strtolower($currency_pair),$pairs)) exit($currency_pair . ' is not a supported currency pair');
    if(!in_array(strtolower($indicator),$indicators)) exit($indicator . ' is not a supported indicator');
    if(!in_array(strtolower($time_frame),$time_frames)) exit($time_frame . ' is not a supported time_frame');
    if(!isset($param)) exit('must provide filter paramaters for indicator');


    switch($indicator)
    {
        case 'tl':
            $indicator_name = 'Trend Detector';

            break;

        case 'cs':
            $indicator_name = 'Strength Meter';
            break;
        case 'rs':
            $indicator_name = 'Scalping Hero';
            switch($param)
            {
                case '1':
                    $param_name = 'Buy Scalp';
                    break;
                case '2':
                    $param_name = 'Sell Scalp';
                    break;
            }
            break;
    }

    /** =======================================================
     *  FETCH THE USERS SUBSCRIBED TO THIS PARTICULAR CONDITION
     *  =======================================================
     */

    $q = 'SELECT user.user_id, user.user_email FROM user INNER JOIN alert ON user.user_id = alert.user_id WHERE alert.alert_is_enabled = 1 AND alert.alert_indicator="' . $indicator .'" AND alert.alert_timeframe="' . $time_frame .'" AND alert.alert_currency_pair="' . $currency_pair .'" AND alert.alert_param="' . $param .'" AND user.user_is_enabled = 1 AND user.user_email_notifications = 1;';

    $result = $db->db_query($q,DATABASE_USER);

    if(empty($result))
    {
        error_log('no one subscribed to this: ' . print_r($data,true));
        return true;
    }


    //iterate through the list of subscribers and add them to a new array
    $email_list = Array();
    foreach($result as $item)
    {
        $email_list[] = $item['user_email'];
    }

    error_log('sending emails to: ' .print_r($email_list,true));


    $data = Array(
        'mail_host'=>MAILER_HOST,
        'mail_username'=>MAILER_USERNAME,
        'mail_password'=>MAILER_PASSWORD,
        'mail_from_email'=>MAILER_USERNAME,
        'mail_from_name'=>MAILER_SENDER_NAME,
        //'mail_to_email'=>'guitarsmith@gmail.com',
        'mail_list'=>$email_list,
        'mail_to_name'=>'Chart Visual User',
        'mail_subject'=>'ALERT: ' . $indicator_name . ' - '. $param_name . ' - '. strtoupper($currency_pair) . ' - ' . strtoupper($time_frame) ,
        'mail_body'=>'You have subscribed to an alert for a Chart Visual product named ' . $indicator_name .'. It has hit ' . $param_name . ' on currency pair ' . strtoupper($currency_pair) . ' within time frame ' . strtoupper($time_frame) . ' at ' .    $datetime  .'.<br><br> Thanks,<br>-Chart Visual Team'
    );

    $result = send_email($data);
    if($result)
    {
     return true;
    }
    else {
        error_log('sending message failed');
        return false;
    }



}





?>
