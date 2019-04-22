<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

require_once("includes/autoload.php");
$lang->setLang($config['default_language']);

if(isset($_GET['id'])) {
	$id = $db->real_escape_string($_GET['id']);
	
	
	if(is_numeric($id)) {
		$sql = $db->query("SELECT * FROM servers WHERE id='{$id}'");
		$row = $sql->fetch_assoc();
		if($sql->num_rows == 0) {
			echo "This website was not found.";
		} else if($row['widget'] == 0) {
			echo "The widget for this website is currently disabled.";
		} else {
			$uptime = "";
			if($row['state'] == 'active' && $row['back_online'] != "0000-00-00 00:00:00") {
				$datetime1 = new DateTime($row['back_online']);
				$datetime2 = new DateTime(date("Y-m-d H:i:s"));
				$date = $datetime1->diff($datetime2);
				$uptime = " and got a total uptime of ".$date->format('%a day(s), %h hour(s) and %i minute(s)');
			}
			
			if($row['state'] == 'down' && $row['last_down'] != "0000-00-00 00:00:00") {
				if($row['back_online'] == "0000-00-00 00:00:00") { $date = date("Y-m-d H:i:s"); }else{ $date = $row['back_online']; }
				if($row['state'] == 'down') { $date = date("Y-m-d H:i:s"); }
				$datetime1 = new DateTime($row['last_down']);
				$datetime2 = new DateTime($date);
				$date = $datetime1->diff($datetime2);
				$uptime = " for ".$date->format('%a day(s), %h hour(s) and %i minute(s)');
			}
			
			if($row['state'] == "active") {
				$row['state'] = "UP";
			} else if($row['state'] == "down") {
				$row['state'] = "DOWN";
			} else {
				$row['state'] = "UNKNOWN";
			}
			
			$count = $db->query("SELECT id FROM server_stats WHERE server_id='{$id}'");
			$avg = "0";
			$uptime_procent = "0";
			$succeed = "0";
			$failed = "0";
			
			if($count->num_rows != "0") {
				$avg_sum = $db->query("SELECT SUM(load_time) AS c FROM server_stats WHERE server_id='{$id}'");
				$avg_sum = $avg_sum->fetch_assoc();
				$avg_num = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}'");
				$avg = round($avg_sum['c'] / $avg_num->num_rows, 3);

				$succeed = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND state='active'");
				$succeed = $succeed->num_rows;
				$failed = $db->query("SELECT * FROM server_stats WHERE server_id='{$id}' AND state='down'");
				$failed = $failed->num_rows;
				$uptime_procent = $function->Uptime($succeed, $failed);
			}
			
			echo "The website <i>{$row['server_url']}</i> is currently <b>{$row['state']}</b>{$uptime}.<br />
				The website is today for {$uptime_procent}% online.<br />
				<b>Last load time:</b> {$row['last_load']} seconds<br />
				<b>Avarage load time:</b> {$avg} seconds";
		}
	} else {
		echo "GET ?id is not an ingenter.";
	}
} else {
	echo "GET ?id was not found";
}
$db->close();
?>