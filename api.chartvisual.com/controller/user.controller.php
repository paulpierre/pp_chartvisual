<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** ===================
 *  user.controller.php
 *  ===================
 *
 *  api.chartvisual.com/user/
 *
 */


/**
 * ADD USER
    curl --data "first_name=John&last_name=Doe&email=johndoe@gmail.com&username=jdoe&phone=939339802339&country=US&sponsor_id=1234&referrer_id=4321&email_notifications=1" http://api.chartvisual.com/user/add
 * UPDATE USER
    curl --data "country=US" http://api.chartvisual/user/update/donaldtrump1@gmail.com
api.chartvisual/user/update/donaldtrump1@gmail.com
 */
/*
include(LIB_PATH . 'wlmapiclass.php');

$wl_instance = new wlmapiclass(WL_URL,WL_API_KEY);
$wl_instance->return_format = 'json';


//print_r($res);

exit();

*/

switch($controllerFunction)
{
    /** ---------------------------------------
     *  http://api.chartvisual.com/user/alert
     *  ---------------------------------------
     */

    case 'alert':


        error_log('POST: ' .print_r($_POST,true));

        if(!isset($_POST['email']) || (!isset($_POST['data']) && !isset($controllerData)) || !isset($controllerID))
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'You must set your email address in the Settings panel and provide an alert to save'
                )
            ));

        $email = $_POST['email'];
        $save_data = str_replace("\\", "",$_POST['data']);

        //error_log('DATA PORTION: ' . $save_data);

        $alerts = json_decode($save_data,true);
        $indicator = strtolower($controllerID);


        //error_log('POST[data]: ' . print_r($alerts,true));

        $count_modified = 0;
        $count_created = 0;


        //lets look up the user via email
        $db = new Database();
        $db_columns = Array(
            'user_id'
        );
        $db_conditions = Array(
            'user_email'=>$email
        );
        $result = $db->db_retrieve('user',$db_columns,$db_conditions,null,false,DATABASE_USER);

        if(empty($result))
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'Unable to find user with email ' . $email
                )
            ));
        }

        $user_id = $result[0]['user_id'];

        //lets grab all the existing alerts for this user
        $db_columns = Array(
            'alert_id',
            'user_id',
            'alert_indicator', //rs, tl, etc.
            'alert_param',//actual data
            'alert_timeframe',
            'alert_currency_pair',
            'alert_is_enabled'
        );
        $db_conditions = Array(
            'user_id'=>$user_id,
            'alert_indicator'=>$indicator
        );

        $db_alerts = $db->db_retrieve('alert',$db_columns,$db_conditions,null,false,DATABASE_USER);

        /**
         *  http://api.chartvisual.com/alert/rs/list
         */
        if(strtolower($controllerData) == 'list')
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> array(
                    'alerts'=>$db_alerts
                )
            ));


        foreach($alerts as $alert)
        {
            $currency_pair = strtolower($alert['pair']);
            $time_frame = strtolower($alert['time']);
            $indicator_type = strtolower($alert['type']);
            $indicator_action  = intval($alert['action']); //1 = enable, 0 = disable, 2 = delete

            $alert_exists = false;

            if(!empty($db_alerts))
            {
                foreach($db_alerts as $db_alert)
                {
                    /**
                     *  UPDATE
                     */
                    //lets check to see if this alert type exists, if so lets update it
                    if(    $db_alert['alert_currency_pair'] == $currency_pair
                        && $db_alert['alert_timeframe'] == $time_frame
                        && $db_alert['alert_param'] == $indicator_type
                    )
                    {

                        $alert_exists = true;

                        $db_columns = Array(
                            'alert_is_enabled'=>$indicator_action,
                            'alert_tmodified'=>current_timestamp()
                        );

                        $db_conditions = Array(
                            'alert_id'=>intval($db_alert['alert_id'])
                        );
                        $result = $db->db_update('alert',$db_columns,$db_conditions,false,'cv_user');
                        $count_modified++;



                    }

                }

            }



            if(!$alert_exists)
            {
                /**
                 *  CREATE
                 */

                $db_columns = Array(
                    'user_id'=>$user_id,
                    'alert_indicator'=>$indicator, //rs, tl, etc.
                    'alert_param'=>$indicator_type,//actual data
                    'alert_timeframe'=>$time_frame,
                    'alert_currency_pair'=>$currency_pair,
                    'alert_is_enabled'=>1,
                    'alert_tmodified'=>current_timestamp(),
                    'alert_tcreate'=>current_timestamp()
                );
                $result = $db->db_create('alert',$db_columns,DATABASE_USER);
                $alert_exists = false; //lets reset this

                $count_created++;
            }


        }

        $msg = '';
        if($count_modified > 0) $msg = 'modified ' . $count_modified . ' alerts.';
        if($count_created > 0) $msg .= ' ' . $count_created . ' alerts created.';


        api_response(array(
            'code'=> RESPONSE_SUCCESS,
            'data'=> array(
                'message'=>'Successfully ' . $msg
            )
        ));

    break;


    /** -----------------------------------
     *  http://api.chartvisual.com/user/postback/<email>/<broker_id>
     *  -----------------------------------
     *  Add a new user to the database
     *  <broker_id> mg = magnum, tr = TR
     */
    case 'postback':
        $email = (isset($controllerID))?$controllerID:$_POST['email'];
        $broker_id = (isset($controllerData))?strtolower($controllerData):strtolower($_POST['broker']);

        error_log('postback - POST: ' . print_r($_POST,true) . ' controllerID: ' . $controllerID);

        if(empty($email)){
            error_log('postback sent, but no email provided.');
            exit('must set email for postback. ex: http://api.chartvisual.com/user/postback/myemail@domain.com/mgm/ or submit POST in param "email" to http://api.chartvisual.com/user/postback ..Thanks :)');
        }
        if(empty($broker_id)) {
            error_log('postback sent, but no broker identifier provided');
            exit('must set broker identifier for postback ex: http://api.chartvisual.com/user/postback/myemail@domain.com/mgm/ or submit POST in param "broker" to http://api.chartvisual.com/user/postback ..Thanks :)');
        }


        $db = new Database();
        $db_columns = Array(
            'user_id',
            'user_first_name',
            'user_last_name',
            'user_email',
            'user_name',
            'user_password',
            'user_settings',
            'user_phone',
            'user_country',
            'user_referrer_id',
            'user_sponsor_id',
            'user_is_enabled',
            'user_broker',
            'user_membership_level',
            'user_tlogin',
            'user_tmodified',
            'user_tcreate',
            'user_email_notifications'
        );
        $db_conditions = Array(
            'user_email'=>$email
        );
        $result = $db->db_retrieve('user',$db_columns,$db_conditions,null,false,DATABASE_USER);

        if(empty($result)) exit('email does not exist :(');

        include(LIB_PATH . 'wlmapiclass.php');


        $brokers = json_decode($result[0]['user_broker'],true);
        $is_enabled = $result[0]['user_is_enabled'];


        $wl_instance = new wlmapiclass(WL_URL,WL_API_KEY);
        $wl_instance->return_format = 'json';



        /**
         *  FIRST TIME BROKER
         */
        if($is_enabled == 0)
        {
            $data = Array(
                'user_login'=>$result[0]['user_email'],
                'user_email'=>$result[0]['user_email'],
                'user_pass'=>$result[0]['user_password'],
                'country'=>$result[0]['user_country'],
                'wpm_login_limit'=>'1',
                'firstname'=>$result[0]['user_first_name'],
                'lastname'=>$result[0]['user_last_name'],
                'Levels'=>Array(MEMBER_SILVER)
            );

            $response = $wl_instance->post('/members',$data);
            $res = json_decode($response,true);
            error_log(print_r($res,true));
            if($res['success'] == 1)
            {
                $user_instance = new User($result[0]['user_id']);
                $user_instance->membership_level = 2;
                if($user_instance->brokers == null || !in_array($broker_id,$user_instance->brokers))
                    $user_instance->brokers[] = $broker_id;
                $user_instance->is_enabled = 1;
                $user_instance->update_user();
            }
        } else {
            /**
             *  LETS UPGRADE THEM TO THE NEXT TIER IF THEY ALREADY HAVE 1 BROKER
             */

            $response = $wl_instance->get('/levels/'. MEMBER_SILVER.'/members');
            $res = json_decode($response,true);
            error_log(print_r($res,true));

            $members = $res['members']['member'];
            $user_id = false;
            foreach($members as $item)
            {
                if(trim($item['user_email']) == trim($email))
                    $user_id = $item['id'];
            }

            if($user_id)
            {
                //lets upgrade them
                $data = Array(
                    'Users'=>Array($user_id)
                );
                //$response = $wl_instance->delete('/levels/'. MEMBER_SILVER.'/members/'.$user_id);
                //error_log('Deleting user from Silver: ' . print_r($response,true));
                $response = $wl_instance->post('/levels/'. MEMBER_GOLD.'/members',$data);
                error_log('UPGRADING USER: '. $user_id .PHP_EOL . print_r($response,true));

            }

            $user_instance = new User($result[0]['user_id']);
            $user_instance->membership_level = 3;
            if($user_instance->brokers == null || !in_array($broker_id,$user_instance->brokers))
                $user_instance->brokers[] = $broker_id;
            $user_instance->is_enabled = 1;
            $user_instance->update_user();
        }

        break;


    /** -----------------------------------
     *  http://api.chartvisual.com/user/add
     *  -----------------------------------
     *  Add a new user to the database
     */
    case 'add':


        $default_settings = Array(
            'cv_notifications_email'=>true,
            'cv_newsfeed_data'=>false,
            'cv_notifications_browser'=>false,
            'cv_notifications_favicon'=>false,
            'cv_notifications_feed'=>false,
            'cv_notifications_sound'=>false,
            'cv_settings_csi'=>'',
            'cv_settings_laser'=>'',
            'cv_settings_scalper'=>'',
            'cv_tick_data'=>'',
            'isAlerts'=>'',
            'isAutoUpdate'=>'',
            'isMouseInfo'=>'',
            'pair'=>'EURUSD',
            'timeframe'=>'H1',
            'toggleCSI'=>true,
            'toggleLaser'=>true,
            'toggleScalper'=>true

        );

        error_log(print_r($_POST,true));

        $user_first_name    = (isset($_POST['firstname']))?$_POST['firstname']:false;
        $user_last_name     = (isset($_POST['lastname']))?$_POST['lastname']:false;
        $user_email         = (isset($_POST['email']))?$_POST['email']:false;
        $user_name          = (isset($_POST['username']))?$_POST['username']:false;
        $user_password      = (isset($_POST['password']))?$_POST['password']:false;

        $user_country       = (isset($_POST['country']))?$_POST['country']:false;
        $user_phone         = (isset($_POST['phone']))?$_POST['phone']:false;
        $user_sponsor_id    = (isset($_POST['sponsor_id']))?$_POST['sponsor_id']:false;
        $user_referrer_id   = (isset($_POST['referrer_id']))?$_POST['referrer_id']:false;


        $user_settings = (isset($_POST['settings']))?$_POST['settings']:$default_settings;
        $user_email_notifications = (isset($_POST['email_notifications']))?$_POST['email_notifications']:false;

        $data = Array(
            'user_first_name'   =>$user_first_name,
            'user_last_name'    =>$user_last_name,
            'user_email'        =>$user_email,
            'user_settings'     =>$user_settings,
            'user_name'         =>$user_name,
            'user_country'      =>$user_country,
            'user_phone'        =>$user_phone,
            'user_sponsor_id'   =>$user_sponsor_id,
            'user_referrer_id'   =>$user_referrer_id,
            'user_password'     =>$user_password,
            'user_tcreate'      =>current_timestamp(),
            'user_tmodified'    =>current_timestamp(),
        );

        error_log(print_r($data,true));

        //lets make sure we were passed something
        if( !$user_first_name ||
            !$user_last_name ||
            !$user_email ||
            !$user_phone ||
            !$user_country

        )
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'Must provide user data to create user'
                )
            ));

        $user_id = false;

        $db_columns = Array('user_email');
        $db_conditions = Array('user_email'=>$user_email);
        $db = new Database();
        $res = $db->db_retrieve('user',$db_columns,$db_conditions,null,false,'cv_user');
        if(empty($res))
        {
            try {
                $user_instance = new User($data);
                $user_id = $user_instance->add_user($user_instance);
                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'data'=> array(
                        'user_id'=>$user_id,
                        'message'=>'User successfully added with user ID: ' . $user_id
                    )
                ));
            } catch(Exception $e)
            {
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array(
                        'message'=>'There was an error adding the user to the system.'
                    )
                ));
            }
        }


        if(!$user_id)
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> array(
                    'message'=>'Duplicate email entry'
                )
            ));



    break;



    /** ----------------------------------------------
     *  http://api.chartvisual.com/user/update/user_email
     *  ----------------------------------------------
     *  Update a user in the database
     */

    case 'update':
        $user_email = $controllerID;

        //lets make sure the user_id is setup right
        if(!isset($user_email))
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'A valid email address must be provided'
                )
            ));


        //lets look up the user
        $db_conditions = Array();
        $db_conditions['user_email'] = $user_email;

        $db_columns = array(
            'user_id',
            'user_email',
        );

        //create DB object
        $db = new Database();
        $result = $db->db_retrieve('user',$db_columns,$db_conditions,null,false,DATABASE_USER);

        //check to see if the user exists
        if(empty($result))
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'User email ' . $user_email . ' not a valid user_email in the system'
                )
            ));

        $user_id = $result[0]['user_id'];

        $data = Array();
        //lets make sure the client has provided us the right data
        $user_first_name            = (isset($_POST['first_name']))?$_POST['first_name']:false;
        $user_last_name             = (isset($_POST['last_name']))?$_POST['last_name']:false;
        $user_email                 = (isset($_POST['email']))?$_POST['email']:false;
        $user_name                  = (isset($_POST['username']))?$_POST['username']:false;
        $user_password              = (isset($_POST['password']))?$_POST['password']:false;
        $user_settings              = (isset($_POST['settings']))?$_POST['settings']:false;
        $user_email_notifications   = (isset($_POST['email_notifications']))?$_POST['email_notifications']:false;
        $user_country               = (isset($_POST['country']))?$_POST['country']:false;
        $user_phone                 = (isset($_POST['phone']))?$_POST['phone']:false;
        $user_sponsor_id            = (isset($_POST['sponsor_id']))?$_POST['sponsor_id']:false;
        $user_referrer_id           = (isset($_POST['referrer_id']))?$_POST['referrer_id']:false;

        if($user_first_name) $data['user_first_name'] = $user_first_name;
        if($user_last_name) $data['user_last_name'] = $user_last_name;
        if($user_email) $data['user_email'] = $user_email;
        if($user_name) $data['user_name'] = $user_name;
        if($user_password) $data['user_password'] = $user_password;
        if($user_settings) $data['user_settings'] = $user_settings;
        if(isset($_POST['email_notifications'])) $data['user_email_notifications'] = $user_email_notifications;
        if($user_country) $data['user_country'] = $user_country;
        if($user_phone) $data['user_phone'] = $user_phone;
        if($user_sponsor_id) $data['user_sponsor_id'] = $user_sponsor_id;
        if($user_referrer_id) $data['user_referrer_id'] = $user_referrer_id;

        if(empty($data))
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'Must provide data to update'
                )
            ));


        try {
            $user_instance = new User($user_id);
            $user_instance->update_user($data);//($user_instance);
            api_response(array(
                'code'=> RESPONSE_SUCCESS,
                'data'=> array(
                    'message'=>'Settings successfully saved!'
                )
            ));
        } catch(Exception $e)
        {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'There was an error updating the user in the system.'
                )
            ));
        }
    break;

    default:


        api_response(array(
            'code'=> RESPONSE_ERROR,
            'data'=> array(
                'message'=>'Not a valid controller object.'
            )
        ));
    break;

}

?>
