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
	// Last modification  08-July-2015
	//------------------------------------------
	error_reporting (0); //<- important
	//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); //<-- comment it
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	
	$pair = getPost('pair');
	$tf = getPost('tf');
	$table = $pair.'_'.$tf;
	
	echo "<h1>$pair $tf - Strength Meter</h1>";
	?>
	
			<table >
				<tr><td>ID</td><td>BULL</td><td>BEAR</td><td>TIME</td></tr>

	
	
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
		$bull = $row['bull'];
		if($bull>0) $bull = "<strong><font color=\"red\">$bull</font></strong>";
		else $bull = "-";
		$bear = $row['bear'];	
		if($bear<0) $bear = "<strong><font color=\"red\">$bear</font></strong>";
		else $bear = "-";
		$time = $row['time'];
	?>

	<?php
		echo "<tr><td>$id</td><td>$bull</td><td>$bear</td><td>$time</td></tr>";
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