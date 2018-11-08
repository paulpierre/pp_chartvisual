<?php
//by Paul Pierre

//so ajax won't bitch at us
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

set_time_limit(0);
ini_set('mysql.connect_timeout',300);
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 300);


//set the timezone to hotforex
date_default_timezone_set('Europe/Amsterdam');

//lets start dat session
session_start();


/** =========
 *  INCLUDES
 *  =========
 */

//set root path
define('API_PATH',getcwd() . '/');

//operational paths
define('LIB_PATH',API_PATH . 'lib/');
define('MODEL_PATH',API_PATH . 'model/');
define('CONTROLLER_PATH',API_PATH . 'controller/');
define('LOG_PATH',API_PATH . '../log/');
define('TMP_PATH',API_PATH . 'tmp/');





define('SUPPORT_EMAIL','##########@##########');  // Gmail Credentials
define('SUPPORT_EMAIL_PASSWORD','##########');
define('SUPPORT_EMAIL_NAME','ChartVisual Support');

//databases
define('DATABASE_CANDLESTICKS','topforex_1k_prices');
define('DATABASE_CSI','topforex_pipguy');
define('DATABASE_LASER','topforex_laser');
define('DATABASE_SCALPER','topforex_scalper');
define('DATABASE_USER','cv_user');


//libraries
include(LIB_PATH . 'database.class.php');
include(LIB_PATH . 'utility.php');
//include(LIB_PATH. 'mail/PHPMailerAutoload.php');




//models
include(MODEL_PATH . 'csi.model.php');
include(MODEL_PATH . 'laser.model.php');
include(MODEL_PATH . 'cv.model.php');
include(MODEL_PATH . 'user.model.php');


//Model stuff
define('SERIALIZE_DATABASE',0);
define('SERIALIZE_JSON',1);


//Response codes
define('RESPONSE_SUCCESS',1);
define('RESPONSE_ERROR',0);

//for debugging purposes
define('DEBUG',false);

//for environment purposes
define('MODE',(isset($_SERVER['MODE']))?$_SERVER['MODE']:'prod');

//define('MODE','local');

//wishlist stuff
define('WL_URL','http://www.chartvisual.com/');
define('WL_API_KEY','##########');

define('MEMBER_PREMIUM',1442073589);
define('MEMBER_SILVER',1457592981);
define('MEMBER_GOLD',1457593230);

//email stuff
define('MAILER_HOST','smtp.gmail.com');
define('MAILER_USERNAME','##########@chartvisual.com');
define('MAILER_PASSWORD','##########');
define('MAILER_SENDER_NAME','Chart Visual Support');


//define('MODE','prod');


/** =================
 *  MYSQL CREDENTIALS
 *  =================
 */

switch(MODE)
{
    case 'local':
        define('WWW_HOST','chartvisual');
        define('API_HOST','api.chartvisual');
        /*
        define('DATABASE_HOST','127.0.0.1');
        define('DATABASE_PORT',3306);
        define('DATABASE_NAME','topforex_1k_prices');
        define('DATABASE_USERNAME','root');
        define('DATABASE_PASSWORD','root');*/
        define('DATABASE_HOST','api.chartvisual');
        define('DATABASE_PORT',3306);
        define('DATABASE_NAME','topforex_1k_prices');
        define('DATABASE_USERNAME','root');
        define('DATABASE_PASSWORD','root');
        break;
    default:
    case 'prod':
        define('WWW_HOST','www.chartvisual.com');
        define('API_HOST','api.chartvisual.com');
        define('DATABASE_HOST','ea.chartvisual.com');//'public IP: ##########
        define('DATABASE_PORT',3306);
        define('DATABASE_NAME','topforex_1k_prices');
        define('DATABASE_USERNAME','root');
        define('DATABASE_PASSWORD','##########');
        break;
}


if(MODE=='prod') error_reporting(0);
if(empty($_SESSION['pairs']))
{


    $cv_instance = new CV();
    $result = $cv_instance->get_currency_pairs();
    unset($cv_instance);

     $_SESSION['pairs'] = $result;



    if(empty($_SESSION['pairs']))
    {
        $_SESSION['pairs'] = array(
            'eurusd',
            'usdjpy',
            'gbpusd',
            'audusd',
            'usdcad',
            'usdchf',
            'nzdusd',
            'gbpjpy',
            'eurjpy',
           // 'eurgbp',
           // 'eurchf'
        );
    }

}
    $currency_pairs_supported = $_SESSION['pairs'];



/**  ==========================
 *   SUPPORTED FOREX TIMEFRAMES
 *   ==========================
 */

$currency_time_frames_supported = array(
    'm1',
    'm5',
    'm15',
    'm30',
    'h1',
    'h4',
    'd1',
    'w1',
    'mn1'
);




/** ==============
 *  ERROR MESSAGES
 *  ==============
 */

define('ERROR_INVALID_PARAMETERS','Invalid parameters passed');
define('ERROR_INVALID_OBJECT','Invalid object');
define('ERROR_INVALID_USER_ID','Invalid ID for object');
define('ERROR_INVALID_FUNCTION','Invalid object function');
define('ERROR_NO_DATA_AVAILABLE','No data available for object');
define('ERROR_PARSING_DATA','An internal error occurred attempting to parse the data from the source');

/** =========
 *  CONSTANTS
 *  =========
 */



/** ===========
 *  URL MAPPING
 *  ===========
 */

//Apache rewrite handler
$q = explode('/',$_SERVER['REQUEST_URI']);
$controllerObject = strtolower((isset($q[1]))?$q[1]:'');
$controllerFunction = strtolower((isset($q[2]))?$q[2]:'');
$controllerID = strtolower((isset($q[3]))?$q[3]:'');
$controllerData = strtolower((isset($q[4]))?$q[4]:'');


/** ==================
 *  CONTROLLER ROUTING
 *  ==================
 */
//Load the object's appropriate controller
$_controller = CONTROLLER_PATH . $controllerObject . '.controller.php';
if(file_exists($_controller))  include($_controller);
    else
    api_response(array(
        'code'=> RESPONSE_ERROR,
        'data'=> array('message'=>ERROR_INVALID_OBJECT)
    ));




/** ============
 *  API RESPONSE
 *  ============
 */
function api_response($res)
{
    $response_code = $res['code'];
    $response_data = $res['data'];

    header('Content-Type: application/json');
    if(DEBUG)
    {
        exit('<pre>' . print_r(
            array(
                'response'=>$response_code,
                'data'=>$response_data

            ),true));
    }
    exit(json_encode(
        array(
            'response'=>$response_code,
            'data'=>$response_data
        )
    ));
}