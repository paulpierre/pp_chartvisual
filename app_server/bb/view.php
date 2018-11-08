<html>

	<head>
		<meta http-equiv="refresh" content="15">
		<!-- Bring to you by http://www.CSSTableGenerator.com -->
		<link rel="stylesheet" href="table.css" type="text/css"/>	
	</head>

	<body>
	<div class="csstable" style="width:90%;height:200%;">
	
<?php
	//------------------------------------------
	// Last modification  18-August-2015
	//------------------------------------------
	//error_reporting (0); //<- important
	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); //<-- comment it
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	
	$pair = getPost('pair');
	$tf = getPost('tf');
	$table = $pair.'_'.$tf;
	
	echo "<h1>$pair $tf - Razor Scalper</h1>";
	?>
	
			<table >
				<tr><td>ID</td><td>Corner</td><td>Time</td></tr>	
	
	<?php
	//------------------------------------------
	// OPEN CONNECTION
	//------------------------------------------
	$connection = mysql_connect($db_server,$db_user,$db_password);
	mysql_select_db($db_database, $connection)or die(mysql_error());	

	$result = mysql_query("SELECT * FROM $table") or die(mysql_error());
	while ($row = mysql_fetch_array($result)) 
	{ 
		$id = $row['id'];
				
		$corner = $row['corner'];
		if($corner!=0) $corner = "<strong><font color=\"blue\">$corner</font></strong>"; else $corner = "-";		
		
		$time = $row['time'];
	?>

	<?php
		echo "<tr><td>$id</td><td>$corner</td><td>$time</td></tr>";
	}
	
	mysql_close($connection);
	
	
//--------------------------------------------------------------------------
// GET POST FUNCTION
//--------------------------------------------------------------------------
	function getPost($name)
	{
		if (!isset($_REQUEST[$name])) {$_REQUEST[$name] = '';} 
		return($_REQUEST[$name]);
	}
//--------------------------------------------------------------------------
	
?>

			</table>
		</div>
		
</body>
	
</html>