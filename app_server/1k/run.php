<?php
//*------------------------------------------------------------------*
// Last modification  12-July-2015
//*------------------------------------------------------------------*
	
	//error_reporting (0);
	error_reporting(E_ALL); // or E_STRICT
//*------------------------------------------------------------------*
	
	require_once ('config.php');
//*------------------------------------------------------------------*
	
	define('SUCCESS','0');
	define('ERR_BAD_AGENT','-1');
	define('ERROR','-2');
//*------------------------------------------------------------------*
	
	//$agent = $_SERVER['HTTP_USER_AGENT'];	
	//if(strstr($agent,"xpPost")==false) bye(ERR_BAD_AGENT);
//*------------------------------------------------------------------*
	
	if (!isset($_REQUEST['filename'])){$_REQUEST['filename'] = '';} 
	$filename = $_REQUEST['filename'];
	$filename = $upload_folder.'/'.$filename;
//*------------------------------------------------------------------*	
			
	$result = restore_tables($filename,$db_server,$db_user,$db_password,$db_database);
	if($result===true) bye(SUCCESS);
	else bye(ERROR);
//*------------------------------------------------------------------*
	
	function restore_tables($file,$host,$user,$pass,$name)
	{
		require("sql_parse.php");
		if(file_exists($file))
		{	
			$link = mysql_connect($host,$user,$pass);
			mysql_select_db($name,$link);
			$sql_query = @fread(@fopen($file, 'r'), @filesize($file));
			//$sql_query = @remove_remarks($sql_query);
			$sql_query = split_sql_file($sql_query, ";");
			for($cnt=0; $cnt<count($sql_query); $cnt++)
			{
				//echo $sql_query[$cnt]."<br>----------------------<br>";
				mysql_query($sql_query[$cnt]) or die(mysql_error());
			}
			mysql_close($link);
			return(true);
		}	
		return(false); //else
	}
//*------------------------------------------------------------------*

	function bye($result)
	{
		echo $result;
		exit;
	}	
//*------------------------------------------------------------------*
?>
