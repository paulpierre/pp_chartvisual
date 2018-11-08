<?

define('BROKER_MAGNUM',1);
define('BROKER_BANCO',2);
define('BROKER_TR',3);


define('MAGNUM_AFF_USER','aff60951');
define('MAGNUM_AFF_PASSWORD','56c20a440e039');
define('MAGNUM_AFF_CAMPAIGN_ID','135');
define('MAGNUM_API_URL','https://api-spotplatform.magnumoptions.eu/Api');

define('BANCO_AFF_USER','chartvisual');
define('BANCO_AFF_PASSWORD','c7R3WZs9dl');
define('BANCO_AFF_CAMPAIGN_ID','26');
define('BANCO_API_URL','http://api-spotplatform.banco-capital.com/api');


define('TR_AFF_USER','Aff#60951');
define('TR_AFF_PASSWORD','56d8615100b7c');
define('TR_AFF_CAMPAIGN_ID','557');
define('TR_API_URL','http://api-spotplatform.trbinaryoptions.com/Api');

include('database.class.php');
include('utility.php');


$submission_error = false;
/**

LOGIN:
https://magnumoptions.eu/signin.php?email=email&password=password

BANCO
curl --data "MODULE=Country&COMMAND=view&api_username=chartvisual&api_password=c7R3WZs9dl&jsonResponse=true" http://api-spotplatform.banco-capital.com/api
curl --data "FirstName=Testes&LastName=McTester&email=guitarsmith@gmail.com&Phone=939339802339&Country=129&currency=USD&password=cv123456789&MODULE=Customer&COMMAND=add&api_username=chartvisual&api_password=c7R3WZs9dl&jsonResponse=true" http://api-spotplatform.banco-capital.com/api

MAGNUM
curl --data "FirstName=Testes&LastName=McTester&email=guitarsmith@gmail.com&Phone=939339802339&Country=129&currency=USD&password=cv123456789&MODULE=Customer&COMMAND=add&api_username=aff60951&api_password=56c20a440e039&jsonResponse=true" https://api-spotplatform.magnumoptions.eu/Api

*/
$ip = $_SERVER['REMOTE_ADDR'];


if(isset($ip))
{

	$url = 'http://ipinfo.io/' . $ip . '/country';
	$raw = file_get_contents($url);
	$user_country = strtolower(trim($raw));
}	
	else $user_country = 'my';
	
//$user_country = 'hk';
	
$raw = json_decode(file_get_contents('countries.json'),true);
foreach ($raw as $item)
{
	if($user_country == trim(strtolower($item['iso'])))
		$country_code = $item['id'];
	else $country_code = 129;
}


if(
	isset($_GET['firstname']) &&
	isset($_GET['lastname']) &&
	isset($_GET['email']) &&
	isset($_GET['phonenumber']) &&
    isset($_GET['password'])
) {

	$first_name = $_GET['firstname'];
	$last_name = $_GET['lastname'];
	$email = $_GET['email'];
	$phone_number = $_GET['phonenumber'];
    $password = $_GET['password'];
    $sponsor_id = (isset($_GET['sponsorid']))?$_GET['sponsorid']:'cv_default';

	
	//$password = strtolower($first_name .'123');
	
	switch($_GET['broker'])
	{
		case 1:
			$user_login_url = 'https://magnumoptions.eu/signin.php?email=' . $email .'&password=' . $password;
			$campaign_id = MAGNUM_AFF_CAMPAIGN_ID;
			$aff_user = MAGNUM_AFF_USER;
			$aff_password = MAGNUM_AFF_PASSWORD;
			$aff_url = MAGNUM_API_URL;
		break;

		case 2:
			$user_login_url = 'https:/banco-capital.com/signin.php?email=' . $email .'&password=' . $password;
			$campaign_id = BANCO_AFF_CAMPAIGN_ID;
			$aff_user = BANCO_AFF_USER;
			$aff_password = BANCO_AFF_PASSWORD;
			$aff_url = BANCO_API_URL;
		break;
		
		case 3:		
			$user_login_url = 'https://trbinaryoptions.com/signin.php?email=' . $email .'&password=' . $password;
			$campaign_id = TR_AFF_CAMPAIGN_ID;
			$aff_user = TR_AFF_USER;
			$aff_password = TR_AFF_PASSWORD;
			$aff_url = TR_API_URL;
		break;
	}
	
	$post = Array(
	    'MODULE' => 'Customer',
	    'COMMAND' => 'add',
		'FirstName'=>$first_name,
		'LastName'=>$last_name,
		'Country'=>$country_code,
		'campaignId'=>$campaign_id,
		'currency'=>'USD',
		'password'=>$password,
		'Phone'=>$phone_number,
		'api_username'=>$aff_user,
		'api_password'=>$aff_password,
		'a_aid'=>$sponsor_id,
		'jsonResponse'=>'true',
		'email'=>$email
	);
	$ch = curl_init($aff_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	$response = curl_exec($ch);
	curl_close($ch);
	error_log(print_r($post,true));
    error_log('RESPONSE' . print_r($response,true));

    //var_dump($response);

    /**
     * {"status":{"connection_status":"successful","operation_status":"failed","errors":["emailAlreadyExists"]}}
     */


    $json = json_decode($response,true);

    if($json['status']['errors'][0] == 'emailAlreadyExists')
    {
        $submission_error = '<div class="row text-center"><div class="alert alert-danger alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>&times;</span><span class="sr-only">Close</span></button>Sorry! This email has already been registered with this broker. Please check your email and use a different address.</div></div><div style="display:none;">' . print_r($response,true) . '</div>';

    }
	else if($json['status']['operation_status'] == 'failed')
		$submission_error = '<div class="row text-center"><div class="alert alert-danger alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>&times;</span><span class="sr-only">Close</span></button>Sorry! It looks like there was an error submitting your form. Please ensure the information below is correct</div></div><div style="display:none;">' . print_r($response,true) . '</div>';
	else if($json['status']['operation_status'] == 'successful')
    {

        $data = Array(
            'firstname'   =>$first_name,
            'lastname'    =>$last_name,
            'email'        =>$email,
            'country'      =>$user_country,
            'phone'        =>$phone_number,
            'referrer_id'  =>$sponsor_id,
            'password'      =>$password
        );

        $ch = curl_init('http://api.chartvisual.com/user/add');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        error_log($response);
        curl_close($ch);

        $res = json_decode($response,true);
        if($res['response'] == 1)
        {
            header('location: ' . $user_login_url);
            exit();
        }
        else
            $submission_error = '<div class="row text-center"><div class="alert alert-danger alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>&times;</span><span class="sr-only">Close</span></button>Sorry! It looks like there was an error submitting your form. Please ensure the information below is correct</div></div><div style="display:none;">' . print_r($response,true) . '</div>';
	}
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ChartVisual.com - Advanced Forex & Binary Options Technologies</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="assets/js/plugins/loaders/pace.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/loaders/blockui.min.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="assets/js/plugins/forms/selects/select2.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/forms/styling/uniform.min.js"></script>

	<script type="text/javascript" src="assets/js/core/app.js"></script>
	<script type="text/javascript" src="assets/js/pages/form_layouts.js"></script>
	<!-- /theme JS files -->


	<script>
	$( document ).ready(function() {
		$('#fn_1').change(function(e){$('#fn_3,#fn_2').val($(this).val());});
		$('#fn_3').change(function(e){$('#fn_1,#fn_2').val($(this).val());});
        $('#fn_2').change(function(e){$('#fn_3,#fn_1').val($(this).val());});


        $('#ln_1').change(function(e){$('#ln_3,#ln_2').val($(this).val());});
		$('#ln_3').change(function(e){$('#ln_1,#ln_2').val($(this).val());});
        $('#ln_2').change(function(e){$('#ln_3,#ln_1').val($(this).val());});


        $('#em_1').change(function(e){$('#em_3,#em2').val($(this).val());});
		$('#em_3').change(function(e){$('#em_1,#em2').val($(this).val());});
        $('#em_2').change(function(e){$('#em_3,#em_1').val($(this).val());});


        $('#ph_1').change(function(e){$('#ph_3,#ph_2').val($(this).val());});
		$('#ph_3').change(function(e){$('#ph_1,#ph_2').val($(this).val());});
        $('#ph_2').change(function(e){$('#ph_3,#ph_1').val($(this).val());});

        $('#sid_1').change(function(e){$('#sid_3,#sid_2').val($(this).val());});
        $('#sid_3').change(function(e){$('#sid_1,#sid_2').val($(this).val());});
        $('#sid_2').change(function(e){$('#sid_3,#sid_1').val($(this).val());});

        $('#pw_1').change(function(e){$('#pw_3,#pw_2').val($(this).val());});
        $('#pw_3').change(function(e){$('#pw_1,#pw_2').val($(this).val());});
        $('#pw_2').change(function(e){$('#pw_3,#pw_1').val($(this).val());});


    });
	</script>

</head>

<body>

	<!-- Main navbar -->
	<div class="navbar navbar-inverse">
		<div class="navbar-header">
			<a class="navbar-brand" href="index.html"><img src="assets/images/logo_light.png" alt=""></a>

			<ul class="nav navbar-nav pull-right visible-xs-block">
				<li><a data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-tree5"></i></a></li>
				<li><a class="sidebar-mobile-main-toggle"><i class="icon-paragraph-justify3"></i></a></li>
			</ul>
		</div>
	</div>
	<!-- /main navbar -->

	<!-- Page container -->
	<div class="page-container">
		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">
				<div class="page-header">
					<div class="page-header-content">
						<div class="page-title text-center">
							<h4>Welcome! Please Select a Broker Below:</h4>
						</div>
					</div>
				</div>


				<!-- Content area -->
				<div class="content text-center">

					<? print $submission_error; ?>

                    <div class="row text-center" style="margin-bottom:20px;">
                        <div id="hfx" class="panel panel-flat col-md-7 text-center"  style="float:none;margin:auto;">
                            <div class="panel-heading  text-center">
                                <h5 class="panel-title">Click the button below to start:</h5>
                            </div>
                            <div class="panel-body text-center">
                                <a href="https://www.hotforex.com/hf/en/landing-pages/100-percent-bonus.html?refid=131722" target="_blank">
                                    <img src="hfx.png"/>
                                </a>
                                <!--<iframe  scrolling="no" width="1000" height="430" src="https://www.hotforex.com/hf/en/landing-pages/100-percent-bonus.html?refid=131722" target="_blank"  style="margin:0 auto;">
                                </iframe>-->
                            </div>

                        </div>
                    </div>


                    <!-- Horizontal form options -->
					<div class="row text-center" style="margin-bottom:20px;">
						<div class="panel panel-flat col-md-7" style="float:none;margin:auto;" id="broker_magnum">
							<div class="row">

								<div class="col-md-12">
									<div class="panel-body">
								<form action="index.php?q=1" class="form-horizontal" style="margin:auto 0;" target="_blank">
									<input type="hidden" name="broker" value="1"/>
									<img src="assets/images/logo_magnum.png"/>

									<br/><br/>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">First Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="firstname" required="required" placeholder="Your first name" id="fn_1">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">Last Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="lastname"  required="required" placeholder="Your first name"  id="ln_1">
										</div>
									</div>



									<!-- Email field -->
									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Email field <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="email" name="email" class="form-control input-rounded" required="required" placeholder="Enter a valid email address"  id="em_1">
										</div>
									</div>
									<!-- /email field -->

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label text-right">Password: <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="password" class="form-control input-rounded" name="password"  required="required"   id="pw_1">
                                        </div>
                                    </div>

									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Phone number<span class="text-danger">*</span></label>
										<div class="col-lg-9">

			                        		<input type="text" class="form-control  input-rounded" name="phonenumber" data-mask="+39 999 999 999" placeholder="Enter your phone in international format"  id="ph_1">
			                        		<span class="help-block">+39 999 999 999</span>
										</div>
									</div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-3 text-right">Sponsor ID </label>
                                        <div class="col-lg-9">
                                            <input type="text" name="sponsorid" class="form-control input-rounded" placeholder="Enter your referral sponsor ID"  id="sid_1">
                                        </div>
                                    </div>

									<div class="text-right">
										<button type="submit" class="btn btn-primary">Continue <i class="icon-arrow-right14 position-left"></i></button>
									</div>
								</form>
												
							</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Horizontal form options -->
					<div class="row" style="margin-bottom:20px;">
						<div class="panel panel-flat col-md-7" style="float:none;margin:auto;" id="broker_tr">
							<div class="row">
					
								<div class="col-md-12">
									<div class="panel-body">
								<form action="index.php?q=3" class="form-horizontal" style="margin:auto 0;" target="_blank">
									<input type="hidden" name="broker" value="3"/>
									<img src="assets/images/logo_tr.png"/>

									<br/><br/>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">First Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="firstname" required="required" placeholder="Your first name" id="fn_3">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">Last Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="lastname"  required="required" placeholder="Your first name"  id="ln_3">
										</div>
									</div>

									<!-- Email field -->
									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Email field <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="email" name="email" class="form-control input-rounded" required="required" placeholder="Enter a valid email address"  id="em_3">
										</div>
									</div>
									<!-- /email field -->

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label text-right">Password: <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="password" class="form-control input-rounded" name="password"  required="required" id="pw_3">
                                        </div>
                                    </div>

									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Phone number<span class="text-danger">*</span></label>
										<div class="col-lg-9">

			                        		<input type="text" class="form-control input-rounded" name="phonenumber" data-mask="+39 999 999 999" placeholder="Enter your phone in international format"  id="ph_3">
			                        		<span class="help-block">+39 999 999 999</span>
										</div>
									</div>

                                    <div class="form-group">
                                        <label class="control-label col-lg-3 text-right">Sponsor ID</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="sponsorid" class="form-control input-rounded" placeholder="Enter your referral sponsor ID"  id="sid_3">
                                        </div>
                                    </div>

									<div class="text-right">
										<button type="submit" class="btn btn-primary">Continue <i class="icon-arrow-right14 position-left"></i></button>
									</div>
								</form>
												
							</div>
								</div>
							</div>
						</div>
					</div>

					
					<div class="row" style="margin-bottom:20px;">
						<div class="panel panel-flat center col-md-6" style="float:none;margin:auto;display:none;"  id="broker_banco">
							<div class="row">
			
								<div class="col-md-12">
									<div class="panel-body">
								<form action="index.php?q=2" class="form-horizontal" style="margin:auto 0;" target="_blank">
									<input type="hidden" name="broker" value="2"/>
									
									<img src="assets/images/logo_banco.png"/>

									<br/><br/>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">First Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="firstname" required="required" placeholder="Your first name"  id="fn_2">
										</div>
									</div>
									<div class="form-group">
										<label class="col-lg-3 control-label text-right">Last Name: <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="text" class="form-control input-rounded" name="lastname"  required="required" placeholder="Your first name"  id="ln_2">
										</div>
									</div>

									<!-- Email field -->
									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Email field <span class="text-danger">*</span></label>
										<div class="col-lg-9">
											<input type="email" name="email" class="form-control input-rounded" required="required" placeholder="Enter a valid email address"  id="em_2">
										</div>
									</div>
									<!-- /email field -->
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label text-right">Password: <span class="text-danger">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="password" class="form-control input-rounded" name="password"  required="required" id="pw_2">
                                        </div>
                                    </div>

									<div class="form-group">
										<label class="control-label col-lg-3 text-right">Phone number<span class="text-danger">*</span></label>
										<div class="col-lg-9">

			                        		<input type="text" class="form-control input-rounded" name="phonenumber" data-mask="+39 999 999 999" placeholder="Enter your phone in international format"  id="ph_2">
			                        		<span class="help-block">+39 999 999 999</span>
										</div>
									</div>


                                    <div class="form-group">
                                        <label class="control-label col-lg-3 text-right">Sponsor ID </label>
                                        <div class="col-lg-9">
                                            <input type="text" name="sponsorid" class="form-control input-rounded" placeholder="Enter your referral sponsor ID"  id="sid_2">
                                        </div>
                                    </div>

									<div class="text-right">
										<button type="submit" class="btn btn-primary">Continue <i class="icon-arrow-right14 position-right"></i></button>
									</div>
								</form>

							</div>
								</div>
							
							</div>
						</div>
						
					</div>
					
					<!-- /vertical form options -->
					

					<!-- Footer -->
					<div class="footer text-muted">
						&copy; 2016. <a href="#">ChartVisual</a>
					</div>
					<!-- /footer -->

				</div>
				<!-- /content area -->

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</div>
	<!-- /page container -->

</body>
</html>
