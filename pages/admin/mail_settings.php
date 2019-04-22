<div class="row marketing">
<?php
if(!$login->Access()) {
	echo "<div class='alert alert-danger'>{$lang->_('NO_PERMISSIONS')}</div>";
}else{
	$email = $db->query("SELECT * FROM mail_settings");
	$email = $email->fetch_assoc();
	
	$type = "php";
	$output = "";
	$type = $email['mail_type'];
	$php_mail = $email['php_mail'];
	$smtp_host = $email['smtp_host'];
	$smtp_port = $email['smtp_port'];
	$smtp_username = $email['smtp_username'];
	$smtp_email = $email['smtp_from'];
	
	if(isset($_POST['option']) && isset($_POST['submit'])) {
		if($_POST['option'] == "php" && isset($_POST['php_email'])) {
			$type = "php";
			$php_mail = $_POST['php_email'];
			if (filter_var($_POST['php_email'], FILTER_VALIDATE_EMAIL)) {
				$php_mail = $db->real_escape_string(html($_POST['php_email']));
				$output = "<div class='alert alert-success'>{$lang->_('SETTINGS_SAVED')}</div>";
				if(DEMO == 0) { $db->query("UPDATE mail_settings SET mail_type='php', php_mail='{$php_mail}'"); }
			} else {
				$output = "<div class='alert alert-danger'>This email address is invalid!</div>";
			}
		} else if($_POST['option'] == "smtp" && isset($_POST['smtp_host']) && isset($_POST['smtp_port']) && isset($_POST['smtp_username']) && isset($_POST['smtp_password']) && isset($_POST['smtp_email'])) {
			$type = "smtp";
			$smtp_host = $db->real_escape_string(html($_POST['smtp_host']));
			$smtp_port = $db->real_escape_string(html($_POST['smtp_port']));
			$smtp_username = $db->real_escape_string(html($_POST['smtp_username']));
			$smtp_password = $db->real_escape_string(html($_POST['smtp_password']));
			$smtp_email = $db->real_escape_string(html($_POST['smtp_email']));
			
			if(DEMO == 0) {
				$mail = new PHPMailer;
				
				$mail->isSMTP();
				$mail->Host = $smtp_host;
				$mail->SMTPAuth = true;
				$mail->Username = $smtp_username;
				$mail->Password = $smtp_password;
				$mail->SMTPSecure = 'tls';
				$mail->Port = $smtp_port;

				$mail->setFrom($smtp_email, $config['name']);
				$mail->addAddress($login->email);

				$mail->isHTML(true);

				$mail->Subject = 'Test email!';
				$mail->Body    = 'This is a test email sent by the Uptime Monitor to see if everything works!';

				if(!$mail->send()) {
					$output = "<div class='alert alert-danger'><b>SMTP error</b><br /> {$mail->ErrorInfo}</div>";
				} else {
					$db->query("UPDATE mail_settings SET mail_type='smtp', smtp_host='{$smtp_host}', smtp_port='{$smtp_port}', smtp_username='{$smtp_username}', smtp_password='".base64_encode($smtp_password)."', smtp_from='{$smtp_email}'");
					$output = "<div class='alert alert-success'>{$lang->_('SETTINGS_SAVED')}</div>";
				}
			} else {
				$output = "<div class='alert alert-success'>{$lang->_('SETTINGS_SAVED')}</div>";
			}
			
		} else {
			$output = "<div class='alert alert-danger'>This option was not found.</div>";
		}
	}
?>
<script>
function SendMailOption(value) {
	if(value == "smtp") {
		$("#php").slideUp(800);
		$("#smtp").slideDown(800);
		
		$('input[name=php_email]').removeAttr('required');
		$('input[name=smtp_host]').attr('required', '');
		$('input[name=smtp_port]').attr('required', '');
		$('input[name=smtp_username]').attr('required', '');
		$('input[name=smtp_password]').attr('required', '');
		$('input[name=smtp_email]').attr('required', '');
	} else if(value == "php") {
		$("#smtp").slideUp(800);
		$("#php").slideDown(800);
		
		$('input[name=php_email]').attr('required', '');
		$('input[name=smtp_host]').removeAttr('required');
		$('input[name=smtp_port]').removeAttr('required');
		$('input[name=smtp_username]').removeAttr('required');
		$('input[name=smtp_password]').removeAttr('required');
		$('input[name=smtp_email]').removeAttr('required');
	}
}
$(function() {
	SendMailOption('<?php echo $type; ?>');
});
</script>
<div class="row" style="margin-top: -20px;">
	<ul class="nav nav-pills">
		<li role="presentation" <?php if($_GET['p'] == "admin/config") { echo 'class="active"'; } ?>><a href="index.php?p=admin/config"><?php echo $lang->_('SYSTEM_CONFIG'); ?></a></li>
		<li role="presentation" <?php if($_GET['p'] == "admin/mail_settings") { echo 'class="active"'; } ?>><a href="index.php?p=admin/mail_settings"><?php echo $lang->_('MAIL_CONFIG'); ?></a></li>
	</ul>
</div>

<div class="row" style="margin-top: 10px;">
	<form method="post">
		<?php echo $output; ?>
		<div class="form-group">
			<label for="name"><?php echo $lang->_('SEND_MAIL_OPTION'); ?></label>
			<div class="radio">
				<label>
					<input type="radio" name="option" value="php" onchange="SendMailOption(this.value);" <?php if($type == "php") { echo 'checked'; } ?>>
					PHP &mdash; <a href="http://php.net/manual/en/function.mail.php" target="_blank">mail()</a>
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="option" value="smtp" onchange="SendMailOption(this.value);" <?php if($type == "smtp") { echo 'checked'; } ?>>
					SMTP &mdash; <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer</a>
				</label>
			</div>
		</div>
		
		<div id="php" style="<?php if($type != "php") { echo 'display: none;'; } ?>">
			<div class="form-group">
				<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
				<input type="email" class="form-control" id="email" name="php_email" value="<?php echo $php_mail; ?>" required>
			</div>
		</div>
		
		<div id="smtp" style="<?php if($type != "smtp") { echo 'display: none;'; } ?>">
			<div class="form-group">
				<label for="host"><?php echo $lang->_('HOST'); ?></label>
				<input type="text" class="form-control" id="host" name="smtp_host" value="<?php echo $smtp_host; ?>" placeholder="e.g. smtp.gmail.com" required>
			</div>
			<div class="form-group">
				<label for="port"><?php echo $lang->_('PORT'); ?></label>
				<input type="number" class="form-control" id="port" name="smtp_port" value="<?php if(empty($email['smtp_port'])) { echo '587'; } else { echo $smtp_port; } ?>" min="1" max="65535" required>
			</div>
			<div class="form-group">
				<label for="username"><?php echo $lang->_('USERNAME'); ?></label>
				<input type="text" class="form-control" id="username" name="smtp_username" onkeyup="$('input[name=smtp_email]').val(this.value)" value="<?php echo $smtp_username; ?>" placeholder="e.g. username@gmail.com" required>
			</div>
			<div class="form-group">
				<label for="password"><?php echo $lang->_('PASSWORD'); ?></label>
				<input type="password" class="form-control" id="password" name="smtp_password" required>
			</div>
			<div class="form-group">
				<label for="email"><?php echo $lang->_('EMAIL'); ?></label>
				<input type="email" class="form-control" id="email" name="smtp_email" value="<?php echo $smtp_email; ?>" placeholder="e.g. from@example.com" required>
			</div>
			<?php echo $lang->_('SMTP_NOTE'); ?>
		</div>

		<button type="submit" name="submit" class="btn btn-success"><?php echo $lang->_('SAVE'); ?></button>
	</form>
</div>
<?php } ?>
</div>