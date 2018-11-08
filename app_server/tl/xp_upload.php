<?php
//*------------------------------------------------------------------*
// Last modification  12-July-2015
//*------------------------------------------------------------------*
	
	error_reporting (0); 	
	//error_reporting(E_ALL); 
//*------------------------------------------------------------------*
	
	require_once ('config.php');
//*------------------------------------------------------------------*
	
	set_time_limit(0);
	ini_set('upload_max_filesize', '500M');
	ini_set('post_max_size', '500M');
	ini_set('max_input_time', 4000); // Play with the values
	ini_set('max_execution_time', 4000); // Play with the values
//*------------------------------------------------------------------*
	
	define('SUCCESS','0');
	define('ERR_BAD_AGENT','-1');
	define('ERR_BAD_PASS','-2');
	define('ERR_UPLOAD','-3');
//*------------------------------------------------------------------*
	
	$agent = $_SERVER['HTTP_USER_AGENT'];	
	if(strstr($agent,"xpPost")==false) bye(ERR_BAD_AGENT);
//*------------------------------------------------------------------*
	
	if (!isset($_REQUEST['pass_key'])){$_REQUEST['pass_key'] = '';} 
	$pass_key = $_POST['pass_key'];
	if(strstr($pass_key,$PassKey)==false) bye(ERR_BAD_PASS);
//*------------------------------------------------------------------*
	
	if(substr($upload_folder,-1,1)!='/') $upload_folder = $upload_folder . '/';
	$uploadfile = $upload_folder . basename($_FILES['userfile']['name']);
	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) bye(SUCCESS);
	else bye(ERR_UPLOAD);
//*------------------------------------------------------------------*
	
	function bye($result)
	{
		echo $result;
		exit;
	}
//*------------------------------------------------------------------*
?>
