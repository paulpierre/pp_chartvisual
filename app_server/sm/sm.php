<?php
	//------------------------------------------
	// Last modification  22-July-2015
	//------------------------------------------
	error_reporting (0); //<- important
	//error_reporting(E_ALL);
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	define('SUCCESS','0');
	define('FIFO','1');	
	define('UPDATED','2');
	define('ERR_UNKNOWN','-1');		
	//------------------------------------------		
	
	//----- POSTS --------------------------	
	$pair = getPost('pair');
	$tf = getPost('tf');
	$bull = getPost('bull');
	$bear = getPost('bear');
	$time = getPost('time');	
	$table = $pair.'_'.$tf;	
	
	//------------------------------------------
	// OPEN CONNECTION
	//------------------------------------------
	$connection = mysql_connect($db_server,$db_user,$db_password);
	mysql_select_db($db_database, $connection)or die(mysql_error());	
	
	//get first row
	$result = mysql_query("SELECT * FROM $table WHERE id='1'");
	$row = mysql_fetch_array($result);
	$first_row_time = $row['time'];
	
	if($first_row_time == $time)
	{
		//update
		$result = mysql_query("UPDATE $table SET bull='$bull', bear='$bear', time='$time' WHERE id='1'") or die(mysql_error());
		bye(UPDATED);		
	}
	else
	{
		//FIFO	
		$result = mysql_query("SELECT * FROM $table");
		$records_count = mysql_num_rows($result);
		$cnt = 0;
		while ($row = mysql_fetch_array($result)) 
		{
			$bulls[$cnt] = $row["bull"];
			$bears[$cnt] = $row["bear"];
			$times[$cnt] = $row["time"];
			$cnt++;
		}
		mysql_free_result($result);
				
		for($cnt=2; $cnt<$records_count; $cnt++)
		{
			$nbull = $bulls[$cnt-2];
			$nbear = $bears[$cnt-2];
			$ntime = $times[$cnt-2];
			$result = mysql_query("UPDATE $table SET bull='$nbull', bear='$nbear', time='$ntime' WHERE id='$cnt'");
		}
		mysql_free_result($result);
		
		$result = mysql_query("UPDATE $table SET bull='$bull', bear='$bear', time='$time' WHERE id='1'") or die(mysql_error());
		mysql_free_result($result);
		
		bye(UPDATED);	
		
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
		global $connection;
		mysql_close($connection);
		exit;
	}	
?>