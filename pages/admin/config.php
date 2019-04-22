<div class="row marketing">
<?php
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}else{
$name = $config['name'];
$timeout = $config['timeout'];
$servers = $config['default_max_servers'];
$captcha = $config['captcha'];
$register = $config['register'];
$language = $config['default_language'];
$history = $config['keep_history'];
$events = $config['keep_events'];
$custom_interval = $config['custom_server_interval'];
$activate = $config['user_activate'];
$date_format = $config['date_format'];
$time_format = $config['time_format'];

if(isset($_POST['submit'])) {
	$name = $db->real_escape_string(html($_POST['name']));
	$timeout = $db->real_escape_string(html($_POST['timeout']));
	$servers = $db->real_escape_string(html($_POST['servers']));
	$captcha = $db->real_escape_string(html($_POST['captcha']));
	$register = $db->real_escape_string(html($_POST['register']));
	$language = $db->real_escape_string(html($_POST['language']));
	$history = $db->real_escape_string(html($_POST['history']));
	$events = $db->real_escape_string(html($_POST['events']));
	$custom_interval = $db->real_escape_string(html($_POST['custom_interval']));
	$activate = $db->real_escape_string(html($_POST['activate']));
	$date_format = $db->real_escape_string(html($_POST['date_format']));
	$time_format = $db->real_escape_string(html($_POST['time_format']));

	if(empty($name) || empty($timeout) || empty($history) || empty($language) || empty($date_format) || empty($time_format) || !is_numeric($timeout) || !is_numeric($servers) || !is_numeric($history) || !is_numeric($events)) {
		echo "<div class='alert alert-danger'>{$lang->_('FORGOT_SOMETHING')}</div>";
		
	} else if(!file_exists("includes/languages/{$language}.txt")) {
		echo "<div class='alert alert-danger'>{$lang->_('LANGUAGE_NOT_FOUND')}</div>";
		
	} else {
		echo "<div class='alert alert-success'>{$lang->_('SETTINGS_SAVED')}</div>";
		if(DEMO == 0) {
			$db->query("UPDATE config SET name='{$name}', default_max_servers='{$servers}', timeout='{$timeout}', captcha='{$captcha}', register='{$register}', default_language='{$language}', keep_history='{$history}', keep_events='{$events}', custom_server_interval='{$custom_interval}', user_activate='{$activate}', date_format='{$date_format}', time_format='{$time_format}'");
		}
	}
}
?>
<ul class="nav nav-pills">
	<li role="presentation" <?php if($_GET['p'] == "admin/config") { echo 'class="active"'; } ?>><a href="index.php?p=admin/config"><?php echo $lang->_('SYSTEM_CONFIG'); ?></a></li>
	<li role="presentation" <?php if($_GET['p'] == "admin/mail_settings") { echo 'class="active"'; } ?>><a href="index.php?p=admin/mail_settings"><?php echo $lang->_('MAIL_CONFIG'); ?></a></li>
</ul>

<form method="post">
	<div class="form-group">
		<label for="name"><?php echo $lang->_('WEBSITE_NAME'); ?></label>
		<input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
	</div>
	
	<div class="form-group">
		<label for="timeout"><?php echo $lang->_('ADMIN_TIMEOUT'); ?></label>
		<div class="input-group">
			<input type="number" min="5" max="20" class="form-control" id="timeout" name="timeout" value="<?php echo $timeout; ?>" required>
			<div class="input-group-addon"><?php echo $lang->_('SECONDS'); ?></div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="servers"><?php echo $lang->_('ADMIN_USER_MAX_SERVERS'); ?></label>
		<input type="number" min="0" max="50" class="form-control" id="servers" name="servers" value="<?php echo $servers; ?>" required>
		<p class="help-block">0 = <?php echo $lang->_('UNLIMITED'); ?></p>
	</div>

	<div class="form-group">
		<label for="sign"><?php echo $lang->_('SIGNIN_CAPTCHA'); ?></label>
		<select name="captcha" id="sign" class="form-control">
			<?php
				echo '<option '.(($captcha == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
				echo '<option '.(($captcha == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
			?>
		</select>
	</div>

	<div class="form-group">
		<label for="register"><?php echo $lang->_('REGISTER_PAGE'); ?></label>
		<select name="register" id="register" class="form-control">
			<?php
				echo '<option '.(($register == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
				echo '<option '.(($register == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="activate"><?php echo $lang->_('ADMIN_USER_ACTIVATE'); ?></label>
		<select name="activate" id="activate" class="form-control">
			<?php
				echo '<option '.(($activate == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
				echo '<option '.(($activate == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="language"><?php echo $lang->_('LANGUAGE'); ?></label>
		<select name="language" id="language" class="form-control">
			<?php
				foreach (glob("includes/languages/*.txt") as $filename) {
					$file = file_get_contents($filename);
					preg_match('/## Language name: ([A-Za-z0-9]+)/', $file, $name);
					
					$code = str_replace(array("includes/languages/", ".txt"), "", $filename);
					echo '<option '.(($language == $code) ? "selected" : "").' value="'.$code.'">'.$name[1].' ('.$code.')</option>';
					
				}
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="check"><?php echo $lang->_('KEEP_HISTORY'); ?></label>
		<div class="input-group">
			<input type="number" class="form-control" id="check" name="history" value="<?php echo $history; ?>" min="1" required>
			<div class="input-group-addon"><?php echo $lang->_('DAYS'); ?></div>
		</div>
	</div>

	<div class="form-group">
		<label for="check"><?php echo $lang->_('KEEP_SERVER_EVENTS'); ?></label>
		<div class="input-group">
			<input type="number" class="form-control" id="check" name="events" value="<?php echo $events; ?>" min="1" required>
			<div class="input-group-addon"><?php echo $lang->_('DAYS'); ?></div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="custom_interval"><?php echo $lang->_('CUSTOM_SERVER_INTERVAL'); ?></label>
		<select name="custom_interval" id="custom_interval" class="form-control">
			<?php
				echo '<option '.(($custom_interval == "1") ? "selected" : "").' value="1">'.$lang->_('YES').'</option>';
				echo '<option '.(($custom_interval == "0") ? "selected" : "").' value="0">'.$lang->_('NO').'</option>';
			?>
		</select>
	</div>
	
	<div class="form-group">
		<label for="date_format"><?php echo $lang->_('DATE_FORMAT'); ?></label>
		<input type="text" class="form-control" id="date_format" name="date_format" value="<?php echo $date_format; ?>" required>
		<p class="help-block"><?php echo $lang->_('FORMATING_INFO'); ?></p>
	</div>
	
	<div class="form-group">
		<label for="time_format"><?php echo $lang->_('TIME_FORMAT'); ?></label>
		<input type="text" class="form-control" id="time_format" name="time_format" value="<?php echo $time_format; ?>" required>
		<p class="help-block"><?php echo $lang->_('FORMATING_INFO'); ?></p>
	</div>
	
	<div class="form-group">
		<label for="cronjob">Cronjob</label>
		<input type="text" class="form-control" id="cronjob" value="<?php echo ((DEMO == 1) ? "Hidden" : "*/1 * * * * /usr/local/bin/php ".PATH."/cronjob/Checker.php >/dev/null 2>&1"); ?>" onclick="this.setSelectionRange(0, this.value.length)" readonly>
	</div>
	
	<div class="form-group">
		<label for="cronjob"><?php echo $lang->_('PANEL_PATH'); ?></label>
		<input type="text" class="form-control" id="cronjob" value="<?php echo ((DEMO == 1) ? "Hidden" : PATH.""); ?>" onclick="this.setSelectionRange(0, this.value.length)" readonly>
	</div>
	
	<button type="submit" name="submit" class="btn btn-success"><?php echo $lang->_('SAVE'); ?></button>
</form>
<?php } ?>
</div>