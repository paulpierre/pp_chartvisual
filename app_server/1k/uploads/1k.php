<?php
	//------------------------------------------
	// Last modification  17-July-2015
	//------------------------------------------
	error_reporting (0); //<- important
	//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); //<-- comment it
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	define(SUCCESS,'0');
	define(FIFO,'1');	
	define(UPDATED,'2');
	define(ERR_UNKNOWN,'-1');		
	//------------------------------------------		
	
	//----- POSTS --------------------------	
	$pair = getPost('pair');
	$tf = getPost('tf');
	$close = getPost('close');
	$open = getPost('open');
	$high = getPost('high');
	$low = getPost('low');
	$time = getPost('time');	
	$table = $pair.'_'.$tf;	
	
	
	//echo "pair=$pair  tf=$tf  close=$close  open=$open  high=$high  low=$low  time=$time  table=$table";	
	//exit;
		
	//------------------------------------------
	// OPEN CONNECTION
	//------------------------------------------
	$connection = mysql_connect($db_server,$db_user,$db_password);
	mysql_select_db($db_database, $connection)or die(mysql_error());	
	
	//get first row
	$result = mysql_query("SELECT * FROM $table WHERE id='1'");
	$row = mysql_fetch_array($result);
	$first_row_time = $row['time'];
	
	/*if($first_row_time == $time) echo "yes   "; else echo "no   ";
	echo $time."      ".$first_row_time;
	exit;*/	
	//$result = mysql_query("SELECT * FROM $table WHERE time LIKE '$time'");
	//$row = mysql_fetch_array($result);
	
	echo strtotime($time)."    <>  ".strtotime($first_row_time);
	
	if($first_row_time == $time)
	{
		//update
		$result = mysql_query("UPDATE $table SET close='$close', open='$open', low='$low', high='$high' WHERE time='$time'") or die(mysql_error());
		bye(UPDATED);		
	}
	else
	{
		//FIFO		
		bye(FIFO);		
	}
	
	bye(SUCCESS);
	
	//------------------------------------------
	// GET POST FUNCTION
	//------------------------------------------
	function getPost($name)
	{
		if (!isset($_REQUEST[$name])) {$_REQUEST[$name] = '';} 
		return($_REQUEST[$name]);
	}
	

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