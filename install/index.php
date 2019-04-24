<?php
require_once("../config.php");
require_once("../includes/classes/Functions.class.php");
$function = new Functions;
require_once("install.class.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<meta name="author" content="Justin991q">

	<title>Albaweb Website Uptime Monitor - Installer</title>

	<link rel="icon" href="../assets/images/icon.png">
	
	<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="../assets/css/jumbotron-narrow.css" rel="stylesheet">
	<link href="../assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">

	<script src="../assets/js/jquery.min.js"></script>
	<script src="../assets/js/bootstrap.min.js"></script>
	
	<script type="text/javascript">
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
			$(".dial").knob();
		})
	</script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
<div class="container">
	<div class="header clearfix">
		<h3 class="text-muted">Albaweb Website Uptime Monitor - Installer</h3>
	</div>
	<div class="row marketing">
		<div class="col-lg-9">
			<form method="post">
				<?php
					if(isset($_POST['back'])) {
						$_SESSION['step'] = $_SESSION['step'] - 1;
					}
				
					if($function->IsInstalled() == 2) {
						echo '<div class="alert alert-danger">This panel is already installed, delete for security reasons the <b>/install/</b> folder!</div>';
					}elseif(@$_SESSION['step'] == '5' || isset($_POST['step5'])) {
					$_SESSION['step'] = '5';
					$db = @new mysqli($_SESSION['db_host'], $_SESSION['db_username'], $_SESSION['db_password'], $_SESSION['db_database']); 

					if($db->connect_error){
						$error = "<b>Error while creating MySQL Connection:</b> ".$db->connect_error;
					}else{
						$sql_contents = file_get_contents(dirname(__FILE__)."/database.sql");
						$sql_contents = explode(";", $sql_contents);
						
						foreach($sql_contents as $query) {
							if(!empty($query)) {
								$db->query($query);
							}
						}
						$db->query("INSERT INTO config (name, timeout, captcha, register, keep_history) VALUES ('".$db->real_escape_string($_SESSION['name'])."', '".$db->real_escape_string($_SESSION['timeout'])."', '".$db->real_escape_string($_SESSION['captcha'])."', '".$db->real_escape_string($_SESSION['register'])."', '".$db->real_escape_string($_SESSION['history'])."')");
						$db->query("INSERT INTO mail_settings (mail_type, php_mail, smtp_host, smtp_port, smtp_username, smtp_password, smtp_from) VALUES ('php', '".$db->real_escape_string($_SESSION['email'])."', '', '', '', '', '');");
						$db->query("INSERT INTO users (username, email, password, admin) VALUES ('".$db->real_escape_string($_SESSION['username'])."', '".$db->real_escape_string($_SESSION['user_email'])."', '".$db->real_escape_string(hash('whirlpool', $_SESSION['password']))."', '1')");
						
						$db->close();
						
						$config = file_get_contents(dirname(__FILE__)."/../config.php");
						$config = str_replace("<db_host>", $_SESSION['db_host'], $config);
						$config = str_replace("<db_username>", $_SESSION['db_username'], $config);
						$config = str_replace("<db_password>", $_SESSION['db_password'], $config);
						$config = str_replace("<db_database>", $_SESSION['db_database'], $config);
						
						file_put_contents(dirname(__FILE__)."/../config.php", $config);
					}
					if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
						$http = "https://";
					}else{
						$http = "http://";
					}
					$domain = str_replace(array("index.php", "/install", "install/"), "", $http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				?>
					<h3>5) Complete Installation</h3>
				<?php
					session_destroy();
					if(!empty($error)) {
						echo "<div class='alert alert-danger'>{$error}<br />Please try the installation <a href='index.php'>again</a>.</div>";
					}else{
				?>
					The installation of Albaweb Website Monitor is successfully completed.<br /><br />
					<b>Panel URL:</b> <a href="<?php echo $domain; ?>"><?php echo $domain; ?></a><br />
					<b>Username:</b> <?php echo $_SESSION['username']; ?><br />
					<b>Password:</b> <?php echo $_SESSION['password']; ?><br /><br />
					
					<h4>Cronjob</h4>
					Do not forget to create a cronjob that runs every 1 minute.<br />
					This cronjob is for to check the server status and get the Uptime every minute.<br /><br />
					
					<b>Cronjob Path:</b>
					<input type="text" class="form-control" id="cronjob" value="<?php echo str_replace("/install", "", dirname(__FILE__)); ?>/cronjob/Checker.php" onclick="this.setSelectionRange(0, this.value.length)" readonly><br />
					
					<b>Cronjob example:</b>
					<input type="text" class="form-control" id="cronjob" value="*/1 * * * * /usr/local/bin/php <?php echo str_replace("/install", "", dirname(__FILE__)); ?>/cronjob/Checker.php >/dev/null 2>&1" onclick="this.setSelectionRange(0, this.value.length)" readonly><br />
			
					<b>Don't know how to create a cronjob? <a href="https://about.linuxtender.com/home/contactme" target="_blank">See our documentation</a>
					
					<br /><br />
					<div class="alert alert-danger">Please delete for security reasons the <b>/install/</b> folder!</div>
				<?php
					}
						
					}elseif(@$_SESSION['step'] == '4' || isset($_POST['step4'])) {
						$_SESSION['step'] = '4';
						$username = "admin";
						$email = "";
						$password = "";
						$password1 = "";
						
						$saved = ((isset($_SESSION['4_saved'])) ? 1 : 0);
						
						if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['password1']) && isset($_POST['user_email'])) {
							$username = $install->html($_POST['username']);
							$email = $install->html($_POST['user_email']);
							$password = $install->html($_POST['password']);
							$password1 = $_POST['password1'];
							
							if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['password1']) || empty($_POST['user_email'])) {
								echo "<div class='alert alert-danger'>You forgot someting.</div>";
								
							}elseif($_POST['password'] != $_POST['password1']) {
								echo "<div class='alert alert-danger'>Your passwords do not match.</div>";
								
							}elseif(!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
								echo "<div class='alert alert-danger'>Please, enter a valid e-mail address</div>";
								
							}else{
								echo "<div class='alert alert-success'>User account is created</div>";
								$_SESSION['username'] = $username;
								$_SESSION['user_email'] = $email;
								$_SESSION['password'] = $password;
								$_SESSION['4_saved'] = 1;
								$saved = 1;
								
							}
						}
				?>
					<h3>4) Admin account</h3>
					<div class="form-group">
						<label for="username">Username</label>
						<input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
					</div>
					
					<div class="form-group">
						<label for="email">E-Mail</label>
						<input type="email" class="form-control" id="email" name="user_email" value="<?php echo $email; ?>" required>
					</div>
					
					<div class="form-group">
						<label for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" value="<?php echo $password; ?>" required>
					</div>
					
					<div class="form-group">
						<label for="password1">Repeat Password</label>
						<input type="password" class="form-control" id="password1" name="password1" value="<?php echo $password1; ?>" required>
					</div>
				<?php
					if($saved == 0) {
						echo '
							<input type="submit" name="step4" class="btn btn-info" value="Save settings">
							<div style="clear: both;"></div><br />
							<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
							<input type="button" class="btn btn-danger pull-right" value="5) Complete Installation" disabled>
						';
					}else{
						echo '
							<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
							<input type="submit" name="step5" class="btn btn-success pull-right" value="5) Complete Installation">
						';
					}						
					}elseif(@$_SESSION['step'] == '3' || isset($_POST['step3'])) {
						$_SESSION['step'] = '3';
						$name = "Albaweb Website Monitor";
						$email = "uptime@{$_SERVER['HTTP_HOST']}";
						$register = 0;
						$captcha = 1;
						$timeout = 10;
						$history = 7;
						$saved = ((isset($_SESSION['3_saved'])) ? 1 : 0);
						
						if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['timeout'])) {
							$name = $install->html($_POST['name']);
							$email = $install->html($_POST['email']);
							$register = $install->html($_POST['register']);
							$captcha = $install->html($_POST['captcha']);
							$timeout = $install->html($_POST['timeout']);
							$history = $install->html($_POST['history']);
							
							if(empty($_POST['name']) || empty($_POST['email']) || empty($_POST['timeout']) || empty($_POST['history']) || !is_numeric($_POST['timeout']) || !is_numeric($_POST['captcha']) || !is_numeric($_POST['register']) || !is_numeric($_POST['history'])) {
								echo "<div class='alert alert-danger'>You forgot someting.</div>";
								
							}elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
								echo "<div class='alert alert-danger'>Please, enter a valid e-mail address</div>";
								
							}else{
								echo "<div class='alert alert-success'>Settings are saved</div>";
								$_SESSION['name'] = $name;
								$_SESSION['email'] = $email;
								$_SESSION['captcha'] = $captcha;
								$_SESSION['register'] = $register;
								$_SESSION['timeout'] = $timeout;
								$_SESSION['history'] = $history;
								$_SESSION['3_saved'] = 1;
								$saved = 1;
								
							}
						}
				?>
					<h3>3) Website settings</h3>
					<div class="form-group">
						<label for="name">Website Name</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
					</div>
					
					<div class="form-group">
						<label for="email">E-Mail</label>
						<input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
					</div>
					
					
					<div class="form-group">
						<label for="sign">Sign In Captcha</label>
						<select name="captcha" id="sign" class="form-control">
							<?php
								echo '<option '.(($captcha == "1") ? "selected" : "").' value="1">Yes</option>';
								echo '<option '.(($captcha == "0") ? "selected" : "").' value="0">No</option>';
							?>
						</select>
					</div>

					<div class="form-group">
						<label for="register">Register allowed</label>
						<select name="register" id="register" class="form-control">
							<?php
								echo '<option '.(($register == "1") ? "selected" : "").' value="1">Yes</option>';
								echo '<option '.(($register == "0") ? "selected" : "").' value="0">No</option>';
							?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="check">Keep site history for</label>
						<div class="input-group">
							<input type="number" class="form-control" id="check" name="history" value="<?php echo $history; ?>" min="1" required>
							<div class="input-group-addon">day(s)</div>
						</div>
					</div>
					
					<div class="form-group">
						<label for="timeout">Timeout (seconds)</label>
						<input type="number" min="5" max="20" class="form-control" id="timeout" name="timeout" value="<?php echo $timeout; ?>" required>
					</div>
				<?php
					if($saved == 0) {
						echo '
							<input type="submit" name="step3" class="btn btn-info" value="Save settings">
							<div style="clear: both;"></div><br />
							<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
							<input type="button" class="btn btn-danger pull-right" value="4) Admin Account" disabled>
						';
					}else{
						echo '
							<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
							<input type="submit" name="step4" class="btn btn-success pull-right" value="4) Admin Account">
						';
					}
						
					}elseif(@$_SESSION['step'] == '2' || isset($_POST['step2'])) {
						$_SESSION['step'] = '2';
						$error = "";
						$connected = ((isset($_SESSION['2_connected'])) ? 1 : 0);
				
						if(isset($_POST['host']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['database'])) {
							$db = @new mysqli($_POST['host'], $_POST['username'], $_POST['password'], $_POST['database']); 

							if($db->connect_error){
								$error = "<b>Error while creating MySQL Connection:</b> ".$db->connect_error;
							}else{
								
								$_SESSION['db_host'] = $_POST['host'];
								$_SESSION['db_username'] = $_POST['username'];
								$_SESSION['db_password'] = $_POST['password'];
								$_SESSION['db_database'] = $_POST['database'];
								$_SESSION['2_connected'] = 1;
								$connected = 1;
								
								$success = "Connection created!";
								$db->close();
							}
						}
				?>
					<h3>2) MySQL Settings</h3>
					<?php
						if(!empty($error)) {
							echo "<div class='alert alert-danger'>{$error}</div>";
						}elseif(!empty($success)) {
							echo "<div class='alert alert-success'>{$success}</div>";
						}
					?>
					<div class="form-group">
						<label>MySQL Host</label>
						<input type="text" class="form-control" name="host" value="<?php echo((isset($_SESSION['db_host'])) ? $_SESSION['db_host'] : "localhost"); ?>" required>
					</div>
					<div class="form-group">
						<label>MySQL Username</label>
						<input type="text" class="form-control" name="username" onkeyup="$('#database').val(this.value);" value="<?php echo((isset($_SESSION['db_username'])) ? $_SESSION['db_username'] : ""); ?>" maxlength="16" required>
					</div>
					<div class="form-group">
						<label>MySQL Password</label>
						<input type="password" class="form-control" name="password" value="<?php echo((isset($_SESSION['db_password'])) ? $_SESSION['db_password'] : ""); ?>">
					</div>
					<div class="form-group">
						<label>MySQL Database</label>
						<input type="text" class="form-control" name="database" id="database" maxlength="16" value="<?php echo((isset($_SESSION['db_database'])) ? $_SESSION['db_database'] : ""); ?>" required>
					</div>
				<?php
				if($connected == 0) {
					echo '
						<input type="submit" name="step2" class="btn btn-info" value="Test Connection">
						<div style="clear: both;"></div><br />
						<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
						<input type="button" class="btn btn-danger pull-right" value="3) Website settings" disabled>
					';
				}else{
					echo '
						<input type="submit" class="btn btn-success pull-left" name="back" value="Back">
						<input type="submit" name="step3" class="btn btn-success pull-right" value="3) Website settings">
					';
				}
				?>
						
				<?php
					}else{
						if(phpversion() >= "5.2" && phpversion() <= "5.3") {
							$phpversion = "<div class='label label-warning'>".phpversion()."</div> (some features are disabled in this PHP version due to incompatibility)";
						} else if(phpversion() >= "5.3") {
							$phpversion = "<div class='label label-success'>".phpversion()."</div>";
						} else {
							$phpversion = "<div class='label label-danger'>".phpversion()."</div>";
						}
						
						$_SESSION['step'] = '1';
				?>
				<h3>1) Overview</h3>
				
				<b>PHP Version:</b> <?php echo $phpversion; ?><br />
				<b>Config Writeable:</b> <?php echo ((is_writable(dirname(__FILE__)."/../config.php")) ? "<div class='label label-success'>Yes</div>" : "<div class='label label-danger'>No</div> (chmod config.php to 0777)"); ?><br />
				<b>_tmp folder Writeable:</b> <?php echo ((is_writable(dirname(__FILE__)."/../_tmp")) ? "<div class='label label-success'>Yes</div>" : "<div class='label label-danger'>No</div> (chmod the _tmp folder to 0777)"); ?><br />
				<b>cURL Installed:</b> <?php echo ((function_exists("curl_init")) ? "<div class='label label-success'>Yes</div>" : "<div class='label label-danger'>No</div>"); ?><br />
				<b>MySQLi Installed:</b> <?php echo ((function_exists("mysqli_connect")) ? "<div class='label label-success'>Yes</div>" : "<div class='label label-danger'>No</div>"); ?><br />
				<b>PHP Mail Enabled/Installed:</b> <?php echo ((function_exists("mail")) ? "<div class='label label-success'>Yes</div>" : "<div class='label label-warning'>No</div> (the system cannot send emails)"); ?><br /><br />
				
				
				<?php
					if(!$install->Errors()) {
						echo '
							<input type="button" class="btn btn-danger pull-right" value="2) MySQL Settings" disabled><br /><br />
							<div class="pull-right">Before you can continue, you have to repair the errors above the page.</div>
						';
					}else{
						echo '<input type="submit" name="step2" class="btn btn-success pull-right" value="2) MySQL Settings">';
					}					
					}
				?>
			</form>
		</div>
		<div class="col-lg-3">
			<ul class="nav nav-pills nav-stacked">
				<li role="presentation" class="<?php echo $install->Steps(1); ?>"><a href="javascript:void(0);">1) Overview <?php echo $install->Steps(1,1); ?></a></li>
				<li role="presentation" class="<?php echo $install->Steps(2); ?>"><a href="javascript:void(0);">2) MySQL Settings <?php echo $install->Steps(2,1); ?></a></li>
				<li role="presentation" class="<?php echo $install->Steps(3); ?>"><a href="javascript:void(0);">3) Website settings <?php echo $install->Steps(3,1); ?></a></li>
				<li role="presentation" class="<?php echo $install->Steps(4); ?>"><a href="javascript:void(0);">4) Admin Account <?php echo $install->Steps(4,1); ?></a></li>
				<li role="presentation" class="<?php echo $install->Steps(5); ?>"><a href="javascript:void(0);">5) Complete Installation <?php echo $install->Steps(5,1); ?></a></li>
			</ul>
		</div>
	</div>

	<footer class="footer">
		<p>&copy; Advanced Website Uptime Monitor <?php echo (date('Y') - 1)." - ".date('Y'); ?>.</p>
	</footer>
</div>
</body>
</html>
