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
	
	echo "<h1>$pair $tf</h1>";
	?>
	
			<table >
				<tr><td>ID</td><td>OPEN</td><td>HIGH</td><td>CLOSE</td><td>LOW</td><td>TIME</td></tr>

	
	
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
		$open = $row['open'];
		$high = $row['high'];			
		$close = $row['close'];
		$open = $row['open'];
		$low = $row['low'];
		$time = $row['time'];
	?>

	<?php
		echo "<tr><td>$id</td><td>$open</td><td>$high</td><td><strong>$close</strong></td><td>$low</td><td>$time</td></tr>";
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