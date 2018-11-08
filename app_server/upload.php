<?php
	//------------------------------------------
	// Last modification  07-July-2015
	//------------------------------------------
	error_reporting (0); //<- important
	//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); //<-- comment it
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	define(SUCCESS,'0');	
	define(ERR_UNKNOWN,'-1');		
	//------------------------------------------		
	
	//----- POSTS --------------------------	
	if (!isset($_REQUEST['pair'])){$_REQUEST['pair'] = "";} 
	if (!isset($_REQUEST['m1'])){$_REQUEST['m1'] = "";} 
	if (!isset($_REQUEST['m5'])){$_REQUEST['m5'] = "";} 
	if (!isset($_REQUEST['m15'])){$_REQUEST['m15'] = "";} 
	if (!isset($_REQUEST['m30'])){$_REQUEST['m30'] = "";} 
	if (!isset($_REQUEST['h1'])){$_REQUEST['h1'] = "";} 
	if (!isset($_REQUEST['h4'])){$_REQUEST['h4'] = "";} 
	if (!isset($_REQUEST['d1'])){$_REQUEST['d1'] = "";} 
	if (!isset($_REQUEST['w1'])){$_REQUEST['w1'] = "";} 
	if (!isset($_REQUEST['mn1'])){$_REQUEST['mn1'] = "";} 
	
	//----- VARS --------------------------	
	$pair = $_REQUEST['pair'];
	$m1 = $_REQUEST['m1'];
	$m5 = $_REQUEST['m5'];
	$m15 = $_REQUEST['m15'];
	$m30 = $_REQUEST['m30'];
	$h1 = $_REQUEST['h1'];
	$h4 = $_REQUEST['h4'];
	$d1 = $_REQUEST['d1'];
	$w1 = $_REQUEST['w1'];
	$mn1 = $_REQUEST['mn1'];
		
	//------------------------------------------
	// OPEN CONNECTION
	//------------------------------------------
	$connection = mysql_connect($db_server,$db_user,$db_password);
	mysql_select_db($db_database, $connection)or die(mysql_error());		
	
	$result = mysql_query("SELECT * FROM $db_data_table WHERE pair='$pair'");
	$row = mysql_fetch_array($result);
	if($row)
	{
		//update
		$result = mysql_query("UPDATE $db_data_table SET m1='$m1', m5='$m5', m15='$m15', m30='$m30', h1='$h1', h4='$h4', d1='$d1', w1='$w1', mn1='$mn1' WHERE pair='$pair'") or die(mysql_error());
		bye(SUCCESS);
	}
	else
	{
		//add
		$result = mysql_query("INSERT INTO $db_data_table (pair,m1,m5,m15,m30,h1,h4,d1,w1,mn1) 
		VALUES('$pair','$m1','$m5','$m15','$m30','$h1','$h4','$d1','$w1','$mn1')") or die(mysql_error());
		bye(SUCCESS);
	}
	
	bye(SUCCESS);

	//------------------------------------------
	// EXIT FUNCTION
	//------------------------------------------
	function bye($result)
	{
		echo $result;
		mysql_close($connection);
		exit;
	}	
?>