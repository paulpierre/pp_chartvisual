<?php





function current_timestamp()
{
    return date("Y/m/d H:i:s");
}

function duration($etime) {

    if ($etime < 1) {
        return 'just now';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
}

 function time_ago($ptime) {
    $etime = time() - $ptime;

    if ($etime < 1) {
        return 'just now';
    }

    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'minute',
        1                       =>  'second'
    );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = number_format($d,1);
            return $r . ' ' . $str . ($r > 1 ? 's' : '');
        }
    }
}

 function aasort (&$array, $key, $sortType=SORT_DESC) {
    if(empty($array)) return true;
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    if($sortType == SORT_DESC)
    {$array=array_reverse($ret,true);}
}

function send_email($data)
{
/**
    $data = Array(
        'mail_host'=>'',
        'mail_username'=>'',
        'mail_password'=>'',
        'mail_from_email'=>'',
        'mail_from_name'=>'',
        'mail_to_email'=>'',
        'mail_to_name'=>'',
        'mail_subject'=>'',
        'mail_body'=>''
    );
**/

    require_once LIB_PATH . 'mail/PHPMailerAutoload.php';


    $mail = new PHPMailer;

    $mail->SMTPDebug = 0;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $data['mail_host'];  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $data['mail_username'];                 // SMTP username
    $mail->Password = $data['mail_password'];                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    //$mail->Port = 25;                                    // TCP port to connect to
    $mail->Port = 587;
    $mail->setFrom($data['mail_from_email'], $data['mail_from_name']);
    //$mail->addAddress($data['mail_to_email'], $data['mail_to_name']);     // Add a recipient
    $mail->addReplyTo($data['mail_from_email'], $data['mail_from_name']);

    //$data['mail_list'][] = 'guitarsmith@gmail.com';
    //$data['mail_list'][] = 'sinsua@gmail.com';

    foreach($data['mail_list'] as $email)
    {
        $mail->addBCC($email,$data['mail_to_name']);
        //$mail->addAddress($email,$data['mail_to_name']);
    }

    $mail->isHTML(true);                                  // Set email format to HTML


    $mail->Subject = $data['mail_subject'];
    $mail->Body    = $data['mail_body'];

    if(!$mail->send()) {
        error_log($mail->ErrorInfo);
        return false;
    } else {
        return true;
    }
}



