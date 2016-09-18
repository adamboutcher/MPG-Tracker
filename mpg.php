<?PHP
date_default_timezone_set('Europe/London');
if (file_exists("mpg.sqlite")) {
	$db = new PDO('sqlite:mpg.sqlite');
} else {
	$tmp = fopen("mpg.sqlite", "w");
	fclose($tmp);
	$db = new PDO('sqlite:mpg.sqlite');
	$db->exec("CREATE TABLE IF NOT EXISTS mpg (id INTEGER PRIMARY KEY AUTOINCREMENT, fueldate TEXT, odo TEXT, litre TEXT, cost TEXT, cpl TEXT, mpg INTEGER)");
	$created=true;
}
if ( ( (isset($_POST['odo'])) ) && ( (isset($_POST['cost']))&&(!empty($_POST['cost'])) ) && ( (isset($_POST['litre']))&&(!empty($_POST['litre'])) ) ) {

	if ( (!isset($_POST['fdate'])) || empty($_POST['fdate']) ) {
		$fdate = date("d-m-Y");
	} else {
		$fdate = date("d-m-Y", $_POST['fdate']);
	}
	$cpl = round(($_POST['cost']/$_POST['litre']),3);
	$qry = $db->query("SELECT litre FROM mpg ORDER BY id DESC LIMIT 1;");
	$sql = $qry->fetchObject();
	$plitre = $sql->litre;
	unset($qry);
	$mpg = round( ($_POST['odo']/($plitre/4.5609)),0); //=ROUND(([odo]/([previous litre]/4.54609)),0)
	$qry = $db->prepare("INSERT INTO mpg ('fueldate','odo','litre','cost','cpl','mpg') VALUES (:fdate, :odo, :litre, :cost, :cpl, :mpg);");
		$qry->bindParam(':fdate',$fdate);
		$qry->bindParam(':odo',$_POST['odo']);
		$qry->bindParam(':litre',$_POST['litre']);
		$qry->bindParam(':cost',$_POST['cost']);
		$qry->bindParam(':cpl',$cpl);
		$qry->bindParam(':mpg',$mpg);
	$added = $qry->execute();
}
if ( (isset($_POST['id']))&&(!empty($_POST['id'])) ){
	unset($qry);
	$qry = $db->prepare("DELETE FROM mpg WHERE `id` = :id;");
		$qry->bindParam(':id',$_POST['id']);
	$deleted = $qry->execute();
}
?>
<html>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>MPG</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous" />
		<style>
		body      { padding:10px 25px; }
		.label-fw { width:75px; }
		.input-group .form-control { height:35px; }
		</style>
	</head>
	<body>
		<h2>MPG</h2>
		<?PHP
		if ((isset($created))&&($created === true)) {
			echo "<div class=\"alert alert-info\">Database Generated.</div>";
		}
		if ((isset($added))&&($added === true)) {
			echo "<div class=\"alert alert-success\">Fuel Up Added - ".$mpg."MPG.</div>";
		}
		if ((isset($deleted))&&($deleted === true)) {
			echo "<div class=\"alert alert-warning\">Fuel Up Removed.</div>";
		}
		?>
		<div class="panel panel-default"><div class="panel-body"><form method="post">
			<div class="input-group">
				<span class="input-group-addon"><label class="label-fw" for="fdate"><span class="glyphicon glyphicon-calendar"></span>&nbsp;Date</label></span><input class="form-control" type="date" name="fdate" id="fdate"/>
			</div><br/>
			<div class="input-group">
				<span class="input-group-addon"><label class="label-fw" for="odo"><span class="glyphicon glyphicon-dashboard"></span>&nbsp;Odo</label></span><input class="form-control" type="number" step="any" min=0 name="odo" id="odo"/>
			</div><br/>
			<div class="input-group">
				<span class="input-group-addon"><label class="label-fw" for="cost"><span class="glyphicon glyphicon-gbp"></span>&nbsp;Cost</label></span><input class="form-control" type="number" step="any" min=0 name="cost" id="cost"/>
			</div><br/>
			<div class="input-group">
				<span class="input-group-addon"><label class="label-fw" for="litre"><span class="glyphicon glyphicon-tint"></span>&nbsp;Litre</label></span><input class="form-control" type="number" step="any" min=0 name="litre" id="litre"/>
			</div><br/>
			<div>
				<div><button class="btn btn-success" type="submit" style="width:100%;"><span class="glyphicon glyphicon-ok"></span>&nbsp;Save Fuel-Up</button></div>
			</div>
		</form></div></div>
		<hr/>
		<div>
		<h2>Past Fuel-Ups</h2>
		<table class="table table-hover table-condensed">
			<tr><th class="hidden-xs hidden-sm hidden-md">Fuel Date</th><th>Odo</th><th>Cost (&pound;)</th><th>Litre</th><th class="hidden-xs hidden-sm hidden-md">Cost/Litre (&pound;)</th><th>MPG</th><th></th></tr>
<?PHP
			$qry = $db->query("SELECT * FROM mpg ORDER BY id DESC;");
			$result = $qry->fetchAll();
			//$result = array_reverse($result);
			$i=0;
			foreach ($result as $row) {
				if ($i < 10) {
					echo "<tr>";
    					echo "<td class=\"hidden-xs hidden-sm hidden-md\">";
    						if ($i == 0) {
    							echo "<span class=\"label label-info\">Latest</span>&nbsp;";
    						}
    						echo $row['fueldate'];
    					echo "</td>";
    					echo "<td>".$row['odo']."</td><td>&pound;".$row['cost']."</td><td>".$row['litre']."</td><td class=\"hidden-xs hidden-sm hidden-md\">&pound;".$row['cpl']."/l</td><td>".$row['mpg']."</td>";
    					echo "<td><form style=\"margin:0;\" method=\"post\"><input type=\"hidden\" name=\"id\" id=\"id\" value=\"".$row['id']."\"/><button class=\"btn btn-danger btn-xs\"type=\"submit\"><span class=\"glyphicon glyphicon-trash\"></span><span class=\"hidden-xs hidden-sm\">&nbsp;Delete</span></form></td>";
    				echo "</tr>";
    			}
    			$total['odo'] = $total['odo']+$row['odo'];
    			$total['cost'] = $total['cost']+$row['cost'];
    			$total['litre'] = $total['litre']+$row['litre'];
    			$i++;
			}
			$total['cpl'] = round(($total['cost']/$total['litre']),3);
			$total['mpg'] = $mpg = round( ($total['odo']/(($total['litre']-$row['litre'])/4.5609)),0);
			echo "<tr><th class=\"hidden-xs hidden-sm hidden-md\">Total/Avg</th><th>".$total['odo']."</th><th>&pound;".$total['cost']."</th><th>".$total['litre']."</th><th class=\"hidden-xs hidden-sm hidden-md\">&pound;".$total['cpl']."/l</th><th>".$total['mpg']."</th><th>&nbsp;</th></tr>";
?>
		</table>
		<p class="text-muted"><strong>Showing:</strong> Last 10 of <em><?PHP echo count($result); ?></em> fuel-ups.</p>
		</div>
	</body>
</html>
