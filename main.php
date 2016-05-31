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
					<option><?php 
						if($tier === 'derelict') {
							echo $type . " (" . $tier . ")";
						}
						else {
							echo $type . " " . $tier; 
						} ?>
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
			<option  selected disabled hidden>plat...</option>
			<option>low</option>
			<option>medium</option>
			<option>high</option>
		</select>
		<input type='submit' name = 'ducats' value='confirm'>
		</p>
	</form>
	
	
	<?php 
	//if ducats form has been submitted
	if(isset($_GET['ducats'])){
		echo "Best ducat farm for " . $_GET['type'] . " with " . $_GET['plat'] . " platinum cost: "; 
	 }
	//if prime parts bar has been submitted
	else if(isset($_GET['parts'])){
		//display the part name, ducats value, and plat price
		$sql = "select * from parts where name='" . $_GET['part'] . "'";
		$result = $con->query($sql);
		for($i = 0; $i < $result->num_rows; $i++) {
			$row = $result->fetch_assoc();
			echo $row["name"] . ": " . $row["ducats"] . " ducats, " .$row["platinum"] . " plat<br>";
			//show any drops from endless towers
			$sql = "select * from endless where ename='" . $_GET['part'] . "'";
			$endless = $con->query($sql);
			for($i = 0; $i < $endless->num_rows; $i++) {
				$erow = $endless->fetch_assoc();
				echo $erow['type'] . " " . $erow['tier'] . " rotation " . $erow['rotation'] . ": " . $erow['chance'] . "%<br>";
			}
			//show any drops from not_endless towers
			$sql = "select * from not_endless where nename='" . $_GET['part'] . "'";
			$not_endless = $con->query($sql);
			for($i = 0; $i < $not_endless->num_rows; $i++) {
				$nerow = $not_endless->fetch_assoc();
				echo $nerow['type'] . " " . $nerow['tier'] . ": " . $nerow['chance'] . "%<br>";
			}
		}
	}
	else if(isset($_GET['towers'])){
		//determine whether the tower is endless
		$type = substr($_GET['tower'], 0, 5);
		$sql = "select endless from towers where type like'" . $type . "%'";
		$result = $con->query($sql);
		$row = $result->fetch_assoc();
		//search endless table for all drops
		if($row['endless']) {
			echo 'you selected an endless tower.';
		}
		//search not_endless table for all drops
		else {
			echo 'you selected a non-endless tower.';
		}
	}
	?>
	
	<p> To do: </p>
	<p> show drops for tower search </p>
	<p> allow to search by set </p>
	<p> allow multiple search items, or new searches to be added on to the bottom </p>
	<p> get the ducats search to show best ducats </p>
	<p> fill the database </p>
	
 </body>
</html>
