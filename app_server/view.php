<html>

	<head>
		<!-- Bring to you by http://www.CSSTableGenerator.com -->
		<link rel="stylesheet" href="table.css" type="text/css"/>	
	</head>

	<body>
	<div class="csstable" style="width:99%;height:200%;">
			<table >
				<tr><td>Pair</td><td>M1</td><td>M5</td><td>M15</td><td>M30</td><td>H1</td><td>H4</td><td>D1</td><td>W1</td><td>MN1</td></tr>

<?php
	//------------------------------------------
	// Last modification  08-July-2015
	//------------------------------------------
	error_reporting (0); //<- important
	//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING)); //<-- comment it
	//------------------------------------------
	require 'config.php';
	//------------------------------------------
	
		
	//------------------------------------------
	// OPEN CONNECTION
	//------------------------------------------
	$connection = mysql_connect($db_server,$db_user,$db_password);
	mysql_select_db($db_database, $connection)or die(mysql_error());	

	$result = mysql_query("SELECT * FROM $db_data_table") or die(mysql_error());
	$odd = false;	
	while ($row = mysql_fetch_array($result)) 
	{ 
		$odd = !$odd;	
		$pair = $row['pair'];
		$m1 = $row['m1'];
		$m5 = $row['m5'];			
		$m15 = $row['m15'];
		$m30 = $row['m30'];
		$h1 = $row['h1'];
		$h4 = $row['h4'];
		$d1 = $row['d1'];
		$w1 = $row['w1'];
		$mn1 = $row['mn1'];
	?>

	<?php
		echo "<tr><td>$pair</td><td>$m1</td><td>$m5</td><td>$m15</td><td>$m30</td><td>$h1</td><td>$h4</td><td>$d1</td><td>$w1</td><td>$mn1</td></tr>";
	}
	
	mysql_close($connection);
	
?>

			</table>
		</div>
		
</body>
	
</html>