<?php

/** ==============
 *  user.model.php
 *  ==============
 */

class User extends Database {
    const TABLE_USER = 'user';


    public $id = null;
    public $first_name = null;
    public $last_name = null;
    public $email = null;
    public $name = null;
    public $password = null;
    public $settings = Array();
    public $is_enabled = 0;
    public $membership_level = 0;
    public $referrer_id = null;
    public $sponsor_id = null;
    public $brokers = Array();
    public $country = null;
    public $phone = null;
    public $date_login = null;
    public $date_modified = null;
    public $date_created = null;
    public $email_notifications = false;


    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_membership_level($level)
    {
        $this->membership_level = $level;
    }

    public function set_country($country)
    {
        $this->country = $country;
    }

    public function set_brokers($brokers)
    {
        $this->brokers = $brokers;
    }

    public function set_phone($phone)
    {
        $this->phone = $phone;
    }

    public function set_referrer_id($id)
    {
        $this->referrer_id = $id;
    }

    public function set_sponsor_id($id)
    {
        $this->sponsor_id = $id;
    }

    public function set_name($name)
    {
        $this->name = $name;
    }

    public function set_email_notifications($is_enabled)
    {
        $this->email_notifications = $is_enabled;
    }

    public function set_password($password)
    {
        $this->password = $password;
    }

    public function set_first_name($first_name)
    {
        $this->first_name = $first_name;
    }

    public function set_last_name($last_name)
    {
        $this->last_name = $last_name;
    }


    public function set_email($email)
    {
        $this->email = $email;
    }

    public function set_settings($settings)
    {
        $this->settings = $settings;
    }

    public function set_is_enabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;
    }

    public function set_date_login($date)
    {
        $this->date_login = $date;
    }

    public function set_date_modified($date)
    {
        $this->date_modified = $date;
    }

    public function set_date_created($date)
    {
        $this->date_created = $date;
    }


    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $data = Array(
            'user_id' => $this->id,
            'user_first_name'=>$this->first_name,
            'user_last_name'=>$this->last_name,
            'user_email'=>$this->email,
            'user_name'=>$this->name,
            'user_membership_level'=>$this->membership_level,
            'user_password'=>$this->password,
            'user_settings'=>json_encode($this->settings),
            'user_phone'=>$this->phone,
            'user_country'=>$this->country,
            'user_referrer_id'=>$this->referrer_id,
            'user_sponsor_id'=>$this->sponsor_id,
            'user_broker'=>json_encode($this->brokers),
            'user_is_enabled'=>$this->is_enabled,
            'user_tlogin'=>$this->date_login,
            'user_tmodified'=>$this->date_modified,
            'user_tcreate'=>$this->date_created,
            'user_email_notifications'=>$this->email_notifications
        );

        switch($type)
        {
            case SERIALIZE_JSON:

                return json_encode($data);
                break;

            case SERIALIZE_DATABASE:
            default:
                return $data;
                break;
        }
    }



    public function __construct($user = null)
    {
        /** ===================
         *  LOAD FROM OBJECT ID
         *  ===================
         *  If a user ID is provided, lets fetch the data for this particular object
         */


        if($user != null && is_numeric($user))
        {
            $user_id = $user;
            $db_conditions = Array();
            $db_conditions['user_id'] = $user_id;

            $db_columns = array(
                'user_id',
                'user_first_name',
                'user_last_name',
                'user_email',
                'user_name',
                'user_password',
                'user_settings',
                'user_phone',
                'user_membership_level',
                'user_country',
                'user_referrer_id',
                'user_sponsor_id',
                'user_broker',
                'user_is_enabled',
                'user_tlogin',
                'user_tmodified',
                'user_tcreate',
                'user_email_notifications'
            );



            $result = $this->db_retrieve(self::TABLE_USER,$db_columns,$db_conditions,null,false,DATABASE_USER);
            if(empty($result[0]))
                throw new Exception('user ID ' . $user_id . ' is not a valid user_id.');



            $this->set_id($user_id);
            $this->set_first_name($result[0]['user_first_name']);
            $this->set_last_name($result[0]['user_last_name']);
            $this->set_email($result[0]['user_email']);
            $this->set_name($result[0]['user_name']);
            $this->set_password($result[0]['user_password']);
            $this->set_settings(json_decode($result[0]['user_settings'],true));
            $this->set_phone($result[0]['user_phone']);
            $this->set_membership_level($result[0]['user_membership_level']);
            $this->set_country($result[0]['user_country']);
            $this->set_brokers(json_decode($result[0]['user_broker'],true));
            $this->set_referrer_id($result[0]['user_referrer_id']);
            $this->set_sponsor_id($result[0]['user_sponsor_id']);
            $this->set_is_enabled($result[0]['user_is_enabled']);
            $this->set_date_login($result[0]['user_tlogin']);
            $this->set_date_created($result[0]['user_tcreate']);
            $this->set_email_notifications($result[0]['user_email_notifications']);
            $this->set_date_modified($result[0]['user_tmodified']);


        } elseif(is_array($user))
        {
            /** =============================
             *  NEW OBJECT FROM ARRAY OF DATA
             *  =============================
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($user as $key=>$val)
            {
                if($key=='user_id')         $this->set_id($val);
                if($key=='user_first_name') $this->set_first_name($val);
                if($key=='user_last_name')  $this->set_last_name($val);
                if($key=='user_email')      $this->set_email($val);
                if($key=='user_name')       $this->set_name($val);
                if($key=='user_broker')     $this->set_brokers($val);
                if($key=='user_membership_level') $this->set_membership_level($val);
                if($key=='user_password')   $this->set_password($val);
                if($key=='user_settings')   $this->set_settings($val);
                if($key=='user_phone')      $this->set_phone($val);
                if($key=='user_country')    $this->set_country($val);
                if($key=='user_sponsor_id') $this->set_sponsor_id($val);
                if($key=='user_referrer_id') $this->set_referrer_id($val);
                if($key=='user_is_enabled') $this->set_is_enabled($val);
                if($key=='user_tlogin')     $this->set_date_login($val);
                if($key=='user_tcreate')    $this->set_date_created($val);
                if($key=='user_tmodified')  $this->set_date_modified($val);
                if($key=='user_email_notifications')    $this->set_email_notifications($val);
            }

        }
    }

    public function update_user($user = null)
    {
        $user_id = null;
        $db_columns = Array();

        if($user == null && !is_numeric($this->id))
            throw new Exception('You must provide an user_id or set an user ID to this user object.');
        if(!is_array($user) && $user == null) $user = $this->id;

        /**
         *  This method can either take an array of valid user table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($user != null && is_array($user))
        {
            $user_id = $this->id;
            $data = $user;
            foreach($data as $key=>$val)
            {
                if($key == 'user_id') $db_columns[$key] = $val;
                if($key == 'user_first_name') $db_columns[$key] = $val;
                if($key == 'user_last_name') $db_columns[$key] = $val;
                if($key == 'user_email') $db_columns[$key] = $val;
                if($key == 'user_name') $db_columns[$key] = $val;
                if($key == 'user_membership_level') $db_columns[$key] = $val;
                if($key == 'user_password') $db_columns[$key] = $val;
                if($key == 'user_settings') $db_columns[$key] = json_encode($val);
                if($key == 'user_broker') $db_columns[$key] = json_encode($val);
                if($key == 'user_phone') $db_columns[$key] = $val;
                if($key == 'user_country') $db_columns[$key] = $val;
                if($key == 'user_sponsor_id') $db_columns[$key] = $val;
                if($key == 'user_referrer_id') $db_columns[$key] = $val;
                if($key == 'user_is_enabled') $db_columns[$key] = $val;
                if($key == 'user_tlogin') $db_columns[$key] = $val;
                if($key == 'user_tcreate') $db_columns[$key] = $val;
                if($key == 'user_email_notifications') $db_columns[$key] = $val;
                if($key == 'user_tmodified') $db_columns[$key] = current_timestamp();
            }
        } elseif($user != null && is_numeric($user))
        {
            $user_id = $user;
            $this->id = $user_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->id != null)                   $db_columns['user_id'] = $this->id;
            if($this->first_name != null)           $db_columns['user_first_name'] = $this->first_name;
            if($this->last_name != null)            $db_columns['user_last_name'] = $this->last_name;
            if($this->email != null)                $db_columns['user_email'] = $this->email;
            if($this->name != null)                 $db_columns['user_name'] = $this->name;
            if($this->membership_level != null)     $db_columns['user_membership_level'] = $this->membership_level;
            if($this->password != null)             $db_columns['user_password'] = $this->password;
            if($this->settings != null)             $db_columns['user_settings'] = json_encode($this->settings);
            if($this->brokers != null)             $db_columns['user_broker'] = json_encode($this->brokers);
            if($this->country !=null)               $db_columns['user_country'] = $this->country;
            if($this->phone !=null)                 $db_columns['user_phone'] = $this->phone;
            if($this->referrer_id !=null)           $db_columns['user_referrer_id'] = $this->referrer_id;
            if($this->sponsor_id !=null)            $db_columns['user_sponsor_id'] = $this->sponsor_id;
            if($this->is_enabled != null)           $db_columns['user_is_enabled'] = $this->is_enabled;
            if($this->date_login != null)           $db_columns['user_tlogin'] = $this->date_login;
            if($this->email_notifications != null)  $db_columns['user_email_notifications'] = $this->email_notifications;
                                                    $db_columns['user_tmodified'] = current_timestamp();
        }

        if(empty($db_columns))
            throw new Exception('No data provided to update user');

        $db_conditions = array('user_id'=>$user_id);

        try {
            $this->db_update(self::TABLE_USER,$db_columns,$db_conditions,false,DATABASE_USER);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }


    public function add_user($user = null)
    {
        /**
         *  $user should be a user object being passed
         */

        if($user instanceof user)
        {
            $db_columns =  $user->serialize_object();
            if(!isset($db_columns['user_id'])) $db_columns['user_id'] = $this->id;

        } else {
            throw new Exception('Not a valid user object!' . print_r($user,true));
        }

        if(isset($db_columns['user_id'])) unset($db_columns['user_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_USER,$db_columns,DATABASE_USER);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }


    public function get_user($id = null)
    {
        if($id == null)
            $db_conditions['user_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['user_id'] = $id;

        $db_columns = array(
            'user_id',
            'user_first_name',
            'user_last_name',
            'user_email',
            'user_name',
            'user_password',
            'user_membership_level',
            'user_phone',
            'user_country',
            'user_broker',
            'user_membership_level',
            'user_sponsor_id',
            'user_referrer_id',
            'user_settings',
            'user_is_enabled',
            'user_tlogin',
            'user_email_notifications',
            'user_tmodified',
            'user_tcreate'
        );

        try {
            $result = $this->db_retrieve(self::TABLE_USER,$db_columns,$db_conditions,null,false,DATABASE_USER);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No user found under user_id ' . $this->id);



        $_placement = new User($result[0]);

        return $_placement;
    }

}







