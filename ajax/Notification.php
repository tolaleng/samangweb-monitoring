<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require_once("../includes/autoload.php");
$lang->setLang($login->lang);

if(!$login->LoggedIn) {
	die("");
}
?>
<script src="assets/js/google.js"></script>
<script type="text/javascript">
<?php
$up = $db->query("SELECT * FROM servers WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND" : "")."  state='active' AND desktop_notif='1'");
while($row = $up->fetch_assoc()) {
	if(isset($_SESSION['down_'.$row['id']])) {
		unset($_SESSION['down_'.$row['id']]);
?>
function up<?php echo $row['id']; ?>() {
	if (!Notification) {
		return;
	}else{
		if (Notification.permission !== "granted") {
			Notification.requestPermission();
		} else {
			
			var notification = new Notification('<?php echo $lang->_('WEBSITE_ONLINE_TITLE', array("%name%" => $row['display_name'])); ?>', {
				icon: 'assets/images/icon.png',
				body: "<?php echo str_ireplace(array("<br />", "<br>", "<br>"), "\\n", $lang->_('WEBSITE_NOTIFICATION_MSG', array("%url%" => $row['server_url'], "%response_code%" => $row['response_code'], "%load_time%" => $row['last_load'], "%date%" => date($config['time_format'], strtotime($row['last_check']))))); ?>",
			});

			notification.onclick = function () {
				window.open("<?php echo $row['server_url']; ?>");			
				notification.close();
			};
			setInterval(function() {
				notification.close();
			}, 9000);
		}
	}
}
up<?php echo $row['id']; ?>();
<?php
	}
}
$down = $db->query("SELECT * FROM servers WHERE ".((!$login->Access()) ? "member_id='{$login->id}' AND" : "")." state='down' AND desktop_notif='1'");
while($row = $down->fetch_assoc()) {
	if(!isset($_SESSION['down_'.$row['id']])) {
		$_SESSION['down_'.$row['id']] = "true";
?>
function down<?php echo $row['id']; ?>() {
	if (!Notification) {
		return;
	}else{
		if (Notification.permission !== "granted") {
			Notification.requestPermission();
		} else {
			document.getElementById('audiotag').play();
			var notification = new Notification('<?php echo $lang->_('WEBSITE_OFFLINE_TITLE', array("%name%" => $row['display_name'])); ?>', {
				icon: 'assets/images/icon.png',
				body: "<?php echo str_ireplace(array("<br />", "<br>", "<br>"), "\\n", $lang->_('WEBSITE_NOTIFICATION_MSG', array("%url%" => $row['server_url'], "%response_code%" => $row['response_code'], "%load_time%" => $row['last_load'], "%date%" => date($config['time_format'], strtotime($row['last_check']))))); ?>",
			});

			notification.onclick = function () {
				window.open("<?php echo $row['server_url']; ?>");			
				notification.close();
			};
			setInterval(function() {
				notification.close();
			}, 9000);
		}
	}
}
down<?php echo $row['id']; ?>();
<?php 
	}
}
?>
</script>
<audio id="audiotag" src="assets/alert.wav" preload="auto"></audio>
<?php
$db->close();
?>