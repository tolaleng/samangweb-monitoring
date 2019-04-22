<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require_once("../includes/autoload.php");
$lang->setLang($login->lang);

if(!$login->LoggedIn) {
	die("<div class='alert alert-danger'>{$lang->_('LOGIN_TOKEN_EXPIRED')}</div>");
}

$sql = $db->query("SELECT * FROM servers ".((!$login->Access()) ? "WHERE member_id='{$login->id}'" : "")." ORDER BY display_name ASC");
$user = $db->query("SELECT * FROM users WHERE id='{$login->id}'")->fetch_assoc();
?>
<script type='text/javascript'>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
</script>
<table class="table table-striped">
	<thead>
		<tr>
			<th><?php echo $lang->_('SERVER'); ?></th>
			<th width="14%"><?php echo $lang->_('SERVER_STATUS'); ?></th>
			<th width="20%"><?php echo $lang->_('TODAY_UPTIME'); ?></th>
			<th width="14%"><?php echo $lang->_('RESPONSE_CODE'); ?></th>
			<th width="20%"><?php echo $lang->_('LOAD_TIME'); ?></th>
			<th width="10%"><?php echo $lang->_('NOTIFICATIONS'); ?></th>
			<th><?php echo $lang->_('OPTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php 
			while($row = $sql->fetch_assoc()) {
				$succeed = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' AND state='active'");
				$succeed = $succeed->num_rows;
				$failed = $db->query("SELECT * FROM server_stats WHERE server_id='{$row['id']}' AND state='down'");
				$failed = $failed->num_rows;
				
				if($row['timeout'] == 0) {
					$timeout = $config['timeout'];
				} else {
					$timeout = $row['timeout'];
				}
		?>
			<tr>
				<td><a href="index.php?p=dashboard&s=<?php echo $row['id']; ?>"><?php echo $row['display_name']; ?></a></td>
				<td><?php if($row['deleted'] == "1") { echo $server->Color("deleted"); }elseif($row['disabled'] == "1") { echo $server->Color("disabled"); }else{ echo $server->Color($row['state']); } ?></td>
				<td>
					<div class="progress" <?php echo $function->Tooltip($lang->_('UPTIME') . ": ".$function->Uptime($succeed, $failed)."%"); ?>>
						<div class="progress-bar active progress-bar-<?php echo $server->Uptime($function->Uptime($succeed, $failed)); ?> progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $function->Uptime($succeed, $failed); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $function->Uptime($succeed, $failed); ?>%;"></div>
					</div>
				</td>
				<td><div class="label label-<?php if($server->ResponseOnline($row['response_code'])) { echo "success"; } else { echo "danger"; }?>"><?php echo $row['response_code']; ?> <span style="margin-left: 3px;" class="fa fa-question-circle" <?php echo $function->Tooltip((($row['response_code'] != "0") ? $server->ResponseName($row['response_code']) : $row['curl_error'])); ?>></span></div></td>
				<td><div class="progress" <?php echo $function->Tooltip($row['last_load']." " . $lang->_('SECONDS')); ?>>
						<div class="progress-bar progress-bar-<?php echo $server->ProgressColor($function->Procent($timeout, $row['last_load'])); ?> progress-bar-striped active" role="progressbar" aria-valuenow="<?php echo $function->Procent($timeout, $row['last_load']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $function->Procent($timeout, $row['last_load']); ?>%;"></div>
					</div>
				</td>
				<td>
					<div class="fa fa-toggle<?php if($row['widget'] == "0") { echo "-off"; } else { echo "-on"; } ?>" <?php echo $function->Tooltip($lang->_('EXTERNAL_WIDGET')); ?>></div>
					<div class="fa fa-bell<?php if($row['desktop_notif'] == "0") { echo "-o"; } ?>" <?php echo $function->Tooltip($lang->_('DESKTOP_NOTIFICATIONS')); ?>></div> 
					<div class="fa fa-envelope<?php if($row['email_to'] == "") { echo "-o"; } ?>" <?php echo $function->Tooltip($lang->_('EMAIL_NOTIFICATIONS')); ?>></div>
					<div class="fa fa-comment<?php if($row['pushbullet'] == "0") { echo "-o"; } ?>" <?php echo $function->Tooltip($lang->_('PUSHBULLET_NOTIFICATIONS')); ?>></div>
				</td>
				<td>
					<?php if($row['deleted'] == "0") { ?>
						<span style="cursor: pointer;" onclick="location.href='index.php?p=dashboard&s=<?php echo $row['id']; ?>'" class="label label-success" <?php echo $function->Tooltip($lang->_('STATISTICS')); ?>><span class="fa fa-line-chart"></span></span>
						<span style="cursor: pointer;" onclick="location.href='index.php?p=server_manager&edit=<?php echo $row['id']; ?>'" class="label label-info" <?php echo $function->Tooltip($lang->_('EDIT')); ?>><span class="fa fa-pencil"></span></span>
						<span style="cursor: pointer;" onclick="location.href='index.php?p=server_manager&delete=<?php echo $row['id']; ?>'" class="label label-danger" <?php echo $function->Tooltip($lang->_('DELETE')); ?>><span class="fa fa-trash"></span></span>
						<?php 
							if($login->Access()) {
								if($row['member_id'] != $login->id) {
								$user = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'")->fetch_assoc();
						?>
							<span class="label label-default" <?php echo $function->Tooltip("{$user['username']} ({$user['email']})"); ?>><span class="fa fa-user"></span></span>
						<?php } } ?>
					<?php }else{ echo $lang->_('NOT_AVAILABLE'); } ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>