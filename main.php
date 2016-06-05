<? //homepage for the warframe site ?>

<html>
 <head>
  <title>Prime Parts</title>
 </head>
 <body>
	<h1>Warframe Prime Parts Tool</h1>
		
	<?php 
		//connect to the prime part database
		$servername = 'localhost';
		$username = 'root';
		$password = 'secret';
		$db = 'warframe';
		
		//create connection
		$con = new mysqli($servername, $username, $password, $db);
		
		
	?>
		
	
	<? //prime parts select box ?>
	<form method="get">
		<p>Search by prime part: 
		<select name='part'>
			<option disabled selected hidden>Select a prime part...</option>
			<?php
				$names = $con->query("select name from parts");
				for($i = 0; $i < $names->num_rows; $i++) {
					$row = $names->fetch_assoc();
					$name = $row['name']; ?>
					<option><?php echo $name; ?></option>
					<?php 
				}
				$sets = $con->query("select distinct sname from sets");
				for($i = 0; $i < $sets->num_rows; $i++){
					$row = $sets->fetch_assoc();
					$set = $row['sname']; ?>
					<option><?php echo $set; ?></option>
					<?php
				}
			?>
		</select>	
		<input type='submit' name = 'parts' value='confirm'>
		</p>
	</form>
	
<? //towers select box ?>
	<form method="get">
		<p>Search by tower: 
		<select name='tower'>
			<option disabled selected hidden>Select a tower...</option>
			<?php
				$towers = $con->query("select type, tier from towers order by type, tier");
				for($i = 0; $i < $towers->num_rows; $i++) {
					$row = $towers->fetch_assoc();
					$type = $row['type']; 
					$tier = $row['tier']; ?>
					<option><?php echo $type . " " . $tier; ?>
					</option>
					<?php 
				}
			?>
		</select>	
		<input type='submit' name = 'towers' value='confirm'>
		</p>
	</form>
	
	<? //ducats bar ?>
	<form  method="get">
		<p>Ducats:
		<select name='type'>
			<option disabled selected hidden>type...</option>
			<option>speed</option>
			<option>efficiency</option>
		</select>
		<select name='plat'>
			<option selected disabled hidden>plat...</option>
			<option>low</option>
			<option>med</option>
			<option>high</option>
		</select>
		<select name='amount'>
			<option selected disabled hidden>results...</option>
			<option>1</option>
			<option>2</option>
			<option>3</option>
			<option>4</option>
			<option>5</option>
			<option>all</option>
		</select>
		<input type='submit' name = 'ducats' value='confirm'>
		</p>
	</form>
	
	
	<?php 
	//if ducats form has been submitted
	if(isset($_GET['ducats'])){
		$type = $_GET['type'];
		$plat = $_GET['plat'];
		$amount = $_GET['amount'];
		echo "Best ducat farm for " . $type . " with " . $plat . " platinum cost:<br>"; 
		
		if($type === 'speed'){
			//low plat only
			if($plat === 'low'){
			$sql = "select truncate(if(strcmp(type, 'sabotage'), sum(ducats * (chance / 100)), sum(ducats * (chance / 100)) * 2), 2) as farm, type, tier from not_endless join parts on nename=name where platinum='low' group by type, tier order by farm desc";
			}
			//low and medium
			else if($plat === 'med'){
				$sql = "select truncate(if(strcmp(type, 'sabotage'), sum(ducats * (chance / 100)), sum(ducats * (chance / 100)) * 2), 2) as farm, type, tier from not_endless join parts on nename=name where platinum='low' or platinum='med' group by type, tier order by farm desc";
			}
			//low, medium, and high
			else if($plat === 'high'){
				$sql = "select truncate(if(strcmp(type, 'sabotage'), sum(ducats * (chance / 100)), sum(ducats * (chance / 100)) * 2), 2) as farm, type, tier from not_endless join parts on nename=name group by type, tier order by farm desc";
			}
			$results = $con->query($sql);
			echo "Ducats per key:<br>";
			if($amount === 'all'){
				$amount = $results->num_rows;
			}
			for($i = 0; $i < $amount; $i++){
				$row = $results->fetch_assoc();
				echo $row['type'] . " " . $row['tier'] . ": " . $row['farm'] . "<br>";
			}
		}
		else if($type === 'efficiency'){
			//temp table is to account for A rotation counting twice
			$con->query("create temporary table rotations select * from endless where rotation='A'");
			$con->query("insert into rotations select * from endless");
			//low plat only
			if($plat === 'low'){
				$sql = "select truncate(sum(ducats * (chance / 100)), 2) as farm, type, tier from rotations join parts on ename=name where platinum='low' group by type, tier order by farm desc";
			}
			//low and medium
			else if($plat === 'med'){
				$sql = "select truncate(sum(ducats * (chance / 100)), 2) as farm, type, tier from rotations join parts on ename=name where platinum='low' or platinum='med' group by type, tier order by farm desc";
			}
			//low, medium, and high
			else if($plat === 'high'){
				$sql = "select truncate(sum(ducats * (chance / 100)), 2) as farm, type, tier from rotations join parts on ename=name group by type, tier order by farm desc";
			}
			$results = $con->query($sql);
			echo "Ducats per full rotation:<br>";
			if($amount === 'all'){
				$amount = $results->num_rows;
			}
			for($i = 0; $i < $amount; $i++){
				$row = $results->fetch_assoc();
				echo $row['type'] . " " . $row['tier'] . ": " . $row['farm'] . "<br>";
			}
		}
	}
	 
	//if prime parts bar has been submitted
	else if(isset($_GET['parts'])){
		//display each part in the set
		$allparts = (array)$_GET['part'];
		echo implode(', ', $allparts) . "<br>";
		foreach($allparts as $part){
			if(strpos($part, ' set') !== false){
				$parts = $con->query("select part, amount from sets where sname='" . $part . "'");
				for($i = 0; $i < $parts->num_rows; $i++) {
					$row = $parts->fetch_assoc();
					display_part($row['part'], $row['amount']);
				}
			}
			//display the single part (amount will always be 1 since it isn't a set)
			else {
				display_part($_GET['part'], 1);
			}
		}
	}
	else if(isset($_GET['towers'])){
		//determine whether the tower is endless
		$type = substr($_GET['tower'], 0, 5);
		$sql = "select endless from towers where type like'" . $type . "%'";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		//split the tower into type and tier
		$tower = explode(" ", $_GET['tower']);
		//accound for mobile defense
		if($tower[0] === 'mobile' and $tower[1] === 'defense'){
			$tower[0] = $tower[0] . " " . $tower[1];
			$tower[1] = $tower[2];
		}
		$type = $tower[0];
		$tier = $tower[1];
		//search endless table for all drops
		if($row['endless']) {
			//function to display drops from a given rotation
			function endless_drops($rot){
				global $con, $type, $tier;
				$drops = $con->query("select * from endless where type='" . $type . "' and tier='" . $tier . "'" . "and rotation='" . $rot . "'");
				for($i = 0; $i < $drops->num_rows; $i++){
				$row = $drops->fetch_assoc();
				echo $row['ename'] . ": " . $row['chance'] . "%<br>";
				}
			}
			echo $type . " " . $tier . "<br>";
			echo "--rotation A--<br>";
			endless_drops('A');
			echo "--rotation B--<br>";
			endless_drops('B');
			echo "--rotation C--<br>";
			endless_drops('C');
			
		}
		//search not_endless table for all drops
		else {
			echo $type . " " . $tier . "<br>";
			$drops = $con->query("select * from not_endless where type='" . $type . "' and tier='" . $tier . "'");
			for($i = 0; $i < $drops->num_rows; $i++){
				$row = $drops->fetch_assoc();
				echo $row['nename'] . ": " . $row['chance'] . "%<br>";
			}
		}
	}
	
	//displays the prime part (name, ducats, plat value, drops)
	function display_part($part, $num) {
		global $con;
		//display the part name, ducats value, and plat price
		$sql = "select * from parts where name='" . $part . "'";
		$result = $con->query($sql);
		for($i = 0; $i < $result->num_rows; $i++) {
			$row = $result->fetch_assoc();
			echo $row["name"] . ": " . $row["ducats"] . " ducats, " .$row["platinum"] . " plat";
			//show amount if greater than 1
			if($num > 1){
				echo " (" . $num . " required)";
			}
			echo "<br>";
			//show any drops from endless towers
			$sql = "select * from endless where ename='" . $part . "'";
			$endless = $con->query($sql);
			for($i = 0; $i < $endless->num_rows; $i++) {
				$erow = $endless->fetch_assoc();
				echo $erow['type'] . " " . $erow['tier'] . " rotation " . $erow['rotation'] . ": " . $erow['chance'] . "%<br>";
			}
			//show any drops from not_endless towers
			$sql = "select * from not_endless where nename='" . $part . "'";
			$not_endless = $con->query($sql);
			for($i = 0; $i < $not_endless->num_rows; $i++) {
				$nerow = $not_endless->fetch_assoc();
				echo $nerow['type'] . " " . $nerow['tier'] . ": " . $nerow['chance'] . "%<br>";
			}
			if($endless->num_rows == 0 and $not_endless->num_rows == 0){
				echo "vaulted";
			}
		}
		echo "<br>";
	}
	?>
	
	<p> To do: </p>
	<p> allow multiple search items, or new searches to be added on to the bottom </p>
	<p> endless ducats shows breakdown by each rotation </p>
	<p> old searches still visible </p>
	<?php /*
	<img src='images/ducats.png'>
	<img src='images/platinum.png'>
	*/ ?>
 </body>
</html>
