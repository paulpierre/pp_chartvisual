<?php
//*------------------------------------------------------------------*
// Last modification  16-Sep-2015
//*------------------------------------------------------------------*
	
	error_reporting (0);
	//error_reporting(E_ALL); // or E_STRICT
//*------------------------------------------------------------------*
	
	require_once ('config.php');
//*------------------------------------------------------------------*

	define('SUCCESS','0');
	define('ERR_BAD_AGENT','-1');
	define('ERROR','-2');
//*------------------------------------------------------------------*	
	
	if (!isset($_REQUEST['filename'])){$_REQUEST['filename'] = '';} 
	$filename = $_REQUEST['filename'];
	$filename = $upload_folder.'/'.$filename;
//*------------------------------------------------------------------*	

	//$agent = $_SERVER['HTTP_USER_AGENT'];	
	//if(strstr($agent,"xpPost")==false) bye(ERR_BAD_AGENT);
//*------------------------------------------------------------------*
	
	//Get the Symbol name
	$symbol = substr($_REQUEST['filename'],0,-4); 
//*------------------------------------------------------------------*
	
	//Parse the CSV file	
	$data = csv_to_array($filename);
//*------------------------------------------------------------------*
	
	//Update the Database
	array_to_sql($data,$count,$symbol);
//*------------------------------------------------------------------*
	
	bye(SUCCESS);
//*------------------------------------------------------------------*
	
	function array_to_sql($array, $count, $symbol)
	{

		//Connect to the Database
		global $db_server, $db_user, $db_password, $db_database;		
		$connection = mysql_connect($db_server,$db_user,$db_password);
		mysql_select_db($db_database, $connection)or die(mysql_error());	
		
		//Create the table 
		$last_table = "";
		for($cnt=0; $cnt<$count; $cnt++)
		{
			$cur_table = $symbol."_".$array[$cnt][0];
			if($last_table!=$cur_table)
			{
				$last_table=$cur_table;
				
				$sql = "DROP TABLE IF EXISTS `".$last_table."`;";
				mysql_query($sql) or die(mysql_error());
				
				//$sql = strTableCreate($last_table);	
				$sql = "CREATE TABLE $last_table(id INT(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
				bull VARCHAR(10) NOT NULL, 		
				bear VARCHAR(10) NOT NULL, 		
				buy VARCHAR(10) NOT NULL, 		
				sell VARCHAR(10) NOT NULL, 		
				av VARCHAR(10) NOT NULL, 		
				mm VARCHAR(10) NOT NULL, 		
				time VARCHAR(30))";
				mysql_query($sql) or die(mysql_error());
			}
			
			//Insert the Data
			$insert = "INSERT INTO `$last_table`(`bull`,`bear`,`buy`,`sell`,`av`,`mm`,`time`) "."VALUES('".$array[$cnt][1]."','".$array[$cnt][2].
			"','".$array[$cnt][3]."','".$array[$cnt][4]."','".$array[$cnt][5]."','".$array[$cnt][6]."','".$array[$cnt][7]."');";
			mysql_query($insert) or die(mysql_error());
			
		}
		
	}
	
	function strTableCreate($table)
	{
		
		
		
		$sql = "CREATE TABLE $table(id INT(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
		bull VARCHAR(10) NOT NULL, 		
		bear VARCHAR(10) NOT NULL, 		
		buy VARCHAR(10) NOT NULL, 		
		sell VARCHAR(10) NOT NULL, 		
		av VARCHAR(10) NOT NULL, 		
		mm VARCHAR(10) NOT NULL, 		
		time VARCHAR(30))";
		
		return($sql);
	}

//*------------------------------------------------------------------*

	function csv_to_array($filename, $delimiter=',')
	{
		if (($handle = fopen($filename, "r")) !== FALSE) 
		{
            $i = 0;
            while (($lineArray = fgetcsv($handle, 4000, $delimiter)) !== FALSE) 
			{
                for ($j=0; $j<count($lineArray); $j++) 
				{
                    $data2DArray[$i][$j] = $lineArray[$j];					
                }
                $i++;
            }
            fclose($handle);
			global $count;
			$count = $i-1;			
        }
        return $data2DArray; 
	}
//*------------------------------------------------------------------*

	function bye($result)
	{
		echo $result;
		exit;
	}	
//*------------------------------------------------------------------*
?>
