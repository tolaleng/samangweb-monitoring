<?php
$debug = 0; // Debug for PHP CLI
$number_of_checks = 3; // Recheck the site when name lookup is timed out

if($debug == 0) { ob_start(); }

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

$dirname = dirname(__FILE__) . "/../";
require_once($dirname . "includes/autoload.php");

function PBText($text) {
	return strip_tags(str_ireplace(array("<br />", "<br>", "<br/>"), "\n", $text));
}

if($current_version != $config['version']) {
	if($debug == 1) {
		die("Check skipped, versions are not matching.\n");
	} else {
		die();
	}
}

/* Runs every time when the cronjob runs aswell. */
$db->query("DELETE FROM reset_password WHERE date <= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$db->query("UPDATE calendar SET state='1' WHERE state='0' AND start_date <= NOW() AND end_date >= NOW()");
$db->query("UPDATE calendar SET state='2' WHERE state='1' AND end_date <= NOW()");

/* At 00:00 the cronjob will clear some databases and save the history for that day. */
if(date("H:i") == "00:00") {
	$db->query("DELETE FROM history WHERE date <= DATE_SUB(NOW(), INTERVAL {$config['keep_history']} DAY)");
	$db->query("DELETE FROM server_events WHERE date <= DATE_SUB(NOW(), INTERVAL {$config['keep_events']} DAY)");
	$db->query("DELETE FROM sessions WHERE date <= DATE_SUB(NOW(), INTERVAL 10 DAY)");

	$sql_history = $db->query("SELECT * FROM servers WHERE disabled='0'");
	while($row = $sql_history->fetch_assoc()) {
		$codes = array();
		
		$succeed = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' AND state='active'");
		$succeed = $succeed->num_rows;
		$failed = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' AND state='down'");
		$failed = $failed->num_rows;
		
		$avg_sum = $db->query("SELECT SUM(load_time) AS c FROM server_stats WHERE server_id='{$row['id']}'");
		$avg_sum = $avg_sum->fetch_assoc();
		$avg_num = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}'");
		$avg = round($avg_sum['c'] / $avg_num->num_rows, 3);
		
		$server_stats = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' GROUP BY response_code ORDER BY response_code DESC");
		while($server_stats_row = $server_stats->fetch_assoc()) {
			$count = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' AND response_code='{$server_stats_row['response_code']}'");
			$count = $count->num_rows;
			
			$codes[$server_stats_row['response_code']]['server_id'] = $row['id'];
			$codes[$server_stats_row['response_code']]['code'] = $server_stats_row['response_code'];
			$codes[$server_stats_row['response_code']]['given'] = $count;
			$codes[$server_stats_row['response_code']]['state'] = $server_stats_row['state'];
		}
		$db->query("INSERT INTO history (server_id, member_id, date, load_average, request_succeed, request_failed, response_codes) VALUES ('{$row['id']}', '{$row['member_id']}', '".date('Y-m-d', strtotime("-1 DAY"))."', '{$avg}', '{$succeed}', '{$failed}', '".json_encode($codes)."')");
	}
	
	$db->query("TRUNCATE server_stats");
	$db->query("UPDATE servers SET last_load='0'");
}


/* Delete temp files older than 30 minutes */
if(file_exists(dirname(__FILE__) . "/../_tmp/")) {
	$files = glob(dirname(__FILE__) . "/../_tmp/*");
	foreach ($files as $file) {
		if (is_file($file)) {
			if (time() - filemtime($file) >= 60 * 30) {
				unlink($file);
			}
		}
	}
}

/* Checking the servers */
$sql = $db->query("SELECT * FROM servers WHERE disabled='0'");
while($row = $sql->fetch_assoc()) {
	if($debug == 1) { echo "{$row['server_url']} (id: {$row['id']}): \n"; }

	$server_calender = $db->query("SELECT * FROM calendar WHERE server_id='{$row['id']}' AND state='1'");
	
	if($row['deleted'] == 1) {
		$db->query("DELETE FROM server_stats WHERE server_id='{$row['id']}'");
		$db->query("DELETE FROM history WHERE server_id='{$row['id']}'");
		$db->query("DELETE FROM server_events WHERE server_id='{$row['id']}'");
		$db->query("DELETE FROM servers WHERE id='{$row['id']}'");
		if($debug == 1) { echo "Removing website \n"; }
	
	} else if($server_calender->num_rows != 0) {
		if($debug == 1) { echo "Check disabled due to the calender.\n"; }
	
	} else if($config['custom_server_interval'] == 1 && date("Y-m-d H:i", strtotime($row['last_check'] . " +{$row['check_time']} MINUTE")) > date("Y-m-d H:i")) {
		if($debug == 1) { echo "Check skipped recheck is on ".date("Y-m-d H:i", strtotime($row['last_check'] . " +{$row['check_time']} MINUTE"))." \n"; }

	} else {
		$user_language = $db->query("SELECT language FROM users WHERE id='{$row['member_id']}'");
		$user_language = $user_language->fetch_assoc();
		if(isset($user_language['language'])) {
			if($user_language['language'] == "") {
				$lang->setLang($config['default_language']);
			} else {
				$lang->setLang($user_language['language']);							
			}
		} else {
			$lang->setLang("en");
		}
		
		$i=1;
		
		$count = 0;
		while($i <= $number_of_checks) {
			$headers = array();
			$headers[] = "Keep-Alive: 300";
			$headers[] = "Connection: Keep-Alive";
			$headers[] = "User-Agent: Advanced Website Uptime Monitor Version " . $config['version'];
			
			if($row['timeout'] == 0) {
				$timeout = $config['timeout'];
			} else {
				$timeout = $row['timeout'];
			}
			
			$ch = curl_init($row['server_url']);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

			$output = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$load_time = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME), 3);
			$curl_errno = curl_errno($ch);
			$curl_error = curl_error($ch);
			curl_close($ch);
				
			if($curl_errno == 6) { $count = $count + 1; }
			if($curl_errno == 28) { $httpcode = 504; }
			
			$unknown_error_msg = $lang->_("CRON_UNKNOWN_ERROR_MSG", array("%curl_errno%" => $curl_errno, "%curl_error%" => $curl_error, "%server_url%" => $row['server_url'], "%httpcode%" => $httpcode));
			$website_online_msg = $lang->_("CRON_ONLINE_MSG", array("%load_time%" => $load_time, "%server_url%" => $row['server_url'], "%httpcode%" => $httpcode));
			$incorrect_resp_code_msg = $lang->_("CRON_OFFLINE_INVALID_RESPONSE_CODE_MSG", array("%load_time%" => $load_time, "%server_url%" => $row['server_url'], "%httpcode%" => $httpcode));

			if($curl_errno != 0) { // cURL Request Failed
				if($curl_errno == 6 && $count != $number_of_checks) {
					if($debug == 1) { echo "# Check {$i}:	Error 6 recheck... (count:{$count})\n"; }
				} else {
					if($debug == 1) { echo "# Check {$i}:	NOT OK! ERROR {$curl_errno} ({$curl_error}) (count:{$count})\n"; }
				
					if($row['state'] != "down") {
						$db->query("UPDATE servers SET last_down=NOW() WHERE id='{$row['id']}'");
						
						# Start Notification
						if(!empty($row['email_to'])) {
							$mail->Send($row['email_to'], $lang->_("CRON_OFFLINE_TITLE", array("%name%" => $row['display_name'])), $unknown_error_msg);
							if($debug == 1) { echo "# Check {$i}:	Email sent\n"; }
						}
						
						if($row['pushbullet'] != "0") {
							$pushbullet = new Pushbullet($pb->IdToToken($row['pushbullet']));
							
							$pushbullet->PushLink($pushbullet->Email, $row['server_url'], $lang->_("CRON_OFFLINE_TITLE", array("%name%" => $row['display_name'])), PBText($unknown_error_msg));
							if($debug == 1) { echo "# Check {$i}:	Pushbullet sent to {$pushbullet->Email}!\n"; }
						}
						# End Notification
					}
					
					$server->LogEvent($row['id'], $row['member_id'], 'down', $httpcode, $db->real_escape_string($curl_error));
					$db->query("UPDATE servers SET last_check=NOW(), last_load='{$load_time}', state='down', response_code='{$httpcode}', curl_error='{$db->real_escape_string($curl_error)}' WHERE id='{$row['id']}'");
					$db->query("INSERT INTO server_stats (server_id, member_id, response_code, load_time, check_date, curl_error, state) VALUES ('{$row['id']}', '{$row['member_id']}', '{$httpcode}', '{$load_time}', NOW(), '{$db->real_escape_string($curl_error)}', 'down')");
					
					break;
				}
			} else {  // cURL Request Succeed
				if($server->ResponseOnline($httpcode)) { // Response Code Listed (online)
					if($row['state'] == "down") {  // check if server is online for the first time
						$db->query("UPDATE servers SET back_online=NOW() WHERE id='{$row['id']}'");
						
						# Start Notification
						if(!empty($row['email_to'])) {
							$mail->Send($row['email_to'], $lang->_("CRON_ONLINE_TITLE", array("%name%" => $row['display_name'])), $website_online_msg);
							if($debug == 1) { echo "# Check {$i}:	Email sent\n"; }
						}
						
						if($row['pushbullet'] != "0") {
							$pushbullet = new Pushbullet($pb->IdToToken($row['pushbullet']));
							$pushbullet->PushLink($pushbullet->Email, $row['server_url'], $lang->_("CRON_ONLINE_TITLE", array("%name%" => $row['display_name'])), PBText($website_online_msg));
							if($debug == 1) { echo "# Check {$i}:	Pushbullet sent to {$pushbullet->Email}!\n"; }
						}
						# End Notification
					}
					
					$server->LogEvent($row['id'], $row['member_id'], 'up', $httpcode, "");
					$db->query("UPDATE servers SET last_check=NOW(), last_load='{$load_time}', state='active', response_code='{$httpcode}', curl_error='' WHERE id='{$row['id']}'");
					$db->query("INSERT INTO server_stats (server_id, member_id, response_code, load_time, check_date, state) VALUES ('{$row['id']}', '{$row['member_id']}', '{$httpcode}', '{$load_time}', NOW(), 'active')");
					if($debug == 1) { echo "# Check {$i}:	[ONLINE] OK! HTTPCODE {$httpcode} (count:{$count})\n"; }
				} else { // Response Code Not Listed (offline)
					if($row['state'] != "down") { // check if server is down for the first time
						$db->query("UPDATE servers SET last_down=NOW() WHERE id='{$row['id']}'");
						
						# Start Notification
						if(!empty($row['email_to'])) {
							$mail->Send($row['email_to'], $lang->_("CRON_OFFLINE_TITLE", array("%name%" => $row['display_name'])), $incorrect_resp_code_msg);
							$db->query("UPDATE servers SET last_down=NOW() WHERE id='{$row['id']}'");
							if($debug == 1) { echo "# Check {$i}:	Email sent\n"; }
						}
						
						if($row['pushbullet'] != "0") {
							$pushbullet = new Pushbullet($pb->IdToToken($row['pushbullet']));
							
							$pushbullet->PushLink($pushbullet->Email, $row['server_url'], $lang->_("CRON_OFFLINE_TITLE", array("%name%" => $row['display_name'])), PBText($incorrect_resp_code_msg));
							if($debug == 1) { echo "# Check {$i}:	Pushbullet sent to {$pushbullet->Email}!\n"; }
						}
						# End Notification
					}
			
					$server->LogEvent($row['id'], $row['member_id'], 'down', $httpcode, "");
					$db->query("UPDATE servers SET last_check=NOW(), last_load='{$load_time}', state='down', response_code='{$httpcode}', curl_error='' WHERE id='{$row['id']}'");
					$db->query("INSERT INTO server_stats (server_id, member_id, response_code, load_time, check_date, state) VALUES ('{$row['id']}', '{$row['member_id']}', '{$httpcode}', '{$load_time}', NOW(), 'down')");
					if($debug == 1) { echo "# Check {$i}:	[OFFLINE] OK! HTTPCODE {$httpcode} (count:{$count})\n"; }
				}
				break; 
			}
			
			if($debug == 1) { echo "# Check {$i}:	Error 6 count: {$count} \n"; }
			$i++;
		}
	}
	if($debug == 1) { echo "\n"; }
}
$db->query("UPDATE config SET last_cron=NOW()");


/* Optimizing Tables in the database */
$tables = $db->query("SHOW TABLES;");
while($table = $tables->fetch_assoc()) { 
	$key = key($table);
	$db->query("OPTIMIZE TABLE {$table[$key]}");
}

$db->close();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
if($debug == 1) { echo "Cronjob took {$total_time} seconds to complete!\n"; }

if($debug == 0) { ob_end_flush(); }
?>