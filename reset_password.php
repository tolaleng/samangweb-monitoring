<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require_once("includes/autoload.php");
if($login->LoggedIn) {
	header("Location: index.php");
	die();
}

$lang->setLang($config['default_language']);

$output = "";
$show_new_password = false;

$username = "";
$email = "";

$password1 = "";
$password2 = "";

$form_names = array("username" => array("required" => 1), "email" => array("required" => 1));
	
if(isset($_GET['code'])) {
	$code = $db->real_escape_string($_GET['code']);
	
	$sql = $db->query("SELECT * FROM reset_password WHERE code='{$code}'");
	$row = $sql->fetch_assoc();
	
	if($sql->num_rows == 0) {
		$output = '<div class="alert alert-danger">'.$lang->_('INVALID_RESET_CODE').'</div>';
	} else {
		$show_new_password = true;
		
		if(isset($_POST['reset_password']) && isset($_POST['password1']) && isset($_POST['password2'])) {
			$password1 = $db->real_escape_string($_POST['password1']);
			$password2 = $db->real_escape_string($_POST['password2']);
			if(strlen($password1) < 6) {
				$output = '<div class="alert alert-danger">' . $lang->_("SIGNUP_LONGER_PASSWORD", array("%char%" => 6)) . '</div>';
				
			} else if($password1 != $password2) {
				$output = '<div class="alert alert-danger">' . $lang->_('SIGNUP_PASSWORD_NOT_MATCH') . '</div>';
				
			} else {
				$output = '<div class="alert alert-success">' . $lang->_('PASSWORD_CHANGED') . '</div>' . $function->Redirect("login.php");
				$db->query("UPDATE users SET password='".hash("whirlpool", $password1)."' WHERE id='{$row['member_id']}'") or die($db->error);
				$db->query("DELETE FROM reset_password WHERE code='{$code}'");
			}
		}
	}
} else if(isset($_POST['reset_password'])) {
	$username = ((!empty($_POST['username'])) ? $db->real_escape_string($_POST['username']) : "");
	$email = ((!empty($_POST['email'])) ? $db->real_escape_string($_POST['email']) : "");
	if($csrf->CheckForms($form_names)) {
		if($csrf->CheckToken()) {
			$captcha = (isset($_POST['captcha']) ? $_POST['captcha'] : '');
			if($config['captcha'] == "1" && $_SESSION['captcha'] != hash("sha256", $captcha)) {
				$output = '<div class="alert alert-danger">'.$lang->_('WRONGCAPTCHA').'</div>';
			}else{
				$sql = $db->query("SELECT * FROM users WHERE username='{$username}' AND email='{$email}'");
				$row = $sql->fetch_assoc();
				
				if($sql->num_rows == 0) {
					$output = '<div class="alert alert-danger">' . $lang->_('RESET_PASSWORD_ERROR_USER_NOT_FOUND') . '</div>';
				} else {
					$requested = $db->query("SELECT * FROM reset_password WHERE member_id='{$row['id']}'");
					
					if($requested->num_rows == 0) {
						$code = md5(uniqid());
						$url = $function->URL(0) . "?code={$code}";
						$message = $lang->_('RESET_PASSOWRD_EMAIL_MESSAGE', array("%title%" => $config['name'], "%url%" => $url, "%username%" => $row['username']));
						$mail->Send($row['email'], $lang->_('RESET_PASSOWRD_EMAIL_TITLE'), $message);
						$db->query("INSERT INTO reset_password (member_id, code, date) VALUES ('{$row['id']}', '{$code}', NOW())");
						$output = '<div class="alert alert-success">' . $lang->_('RESET_PASSWORD_REQUESTED') . '</div>';
					} else {
						$output = '<div class="alert alert-danger">' . $lang->_('RESET_PASSWORD_REQUESTED_FAILED') . '</div>';
					}
				}
			}
		}else{
			$output = '<div class="alert alert-danger">'.$lang->_('INVALIDCRFS').'</div>';
		}
	}else{
		$output = '<div class="alert alert-danger">'.$lang->_('FIELDSREQUIRED').'</div>';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow">
		<meta name="author" content="Justin991q">
		
		<link rel="icon" href="assets/images/icon.png">
	
	
		<title><?php echo $config['name']; ?> - <?php echo $lang->_("RESET_PASSWORD"); ?></title>

		<link href="assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/css/signin.css" rel="stylesheet">
		
		<script src="assets/js/jquery.min.js"></script>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
	<div class="login-body">
    <article class="container-login center-block">
		<section>
			<?php
			if(DEMO == 1) {
				echo '<div class="alert alert-danger">'.$lang->_('DEMO').'</div>';
			}
			?>
			<h2><?php echo $config['name']; ?></h2>
			<ul id="top-bar" class="nav nav-tabs nav-justified">
				<li><a href="login.php"><?php echo $lang->_("SIGNIN"); ?></a></li>
				<li class="active"><a href="reset_password.php"><?php echo $lang->_("RESET_PASSWORD"); ?></a></li>
				<?php if($config['register'] == "1") { ?><li><a href="register.php"><?php echo $lang->_("SIGNUP"); ?></a></li><?php } ?>
			</ul>
			<div class="tab-content tabs-login col-lg-12 col-md-12 col-sm-12 cols-xs-12">
				<div id="login-access" class="tab-pane fade active in">
					<h2><i class="glyphicon glyphicon-log-in"></i> <?php echo $lang->_("RESET_PASSWORD"); ?></h2>
					<?php echo $output; ?>
					
					<form method="post" accept-charset="utf-8" autocomplete="off" role="form" class="form-horizontal">
						<?php if($show_new_password == true) { ?>
							<div class="form-group ">
								<input type="password" class="form-control" name="password1" placeholder="<?php echo $lang->_("NEW_PASSWORD"); ?>" tabindex="1" autofocus />
							</div>
							<div class="form-group ">
								<input type="password" class="form-control" name="password2" placeholder="<?php echo $lang->_("REPEATPASSWORD"); ?>" tabindex="2" />
							</div>
						<?php } else { ?>
							<?php echo $csrf->CreateInput(); ?>
							<div class="form-group ">
								<input type="text" class="form-control" name="username" id="login_value" placeholder="<?php echo $lang->_("USERNAME"); ?>" tabindex="1" value="<?php echo $username; ?>" autofocus />
							</div>
							<div class="form-group ">
								<input type="email" class="form-control" name="email" id="email" placeholder="<?php echo $lang->_("EMAIL"); ?>" value="<?php echo $email; ?>" tabindex="2" />
							</div>
							<?php
							if($config['captcha'] == "1") {
							?>
							<div class="form-group ">
								<label for="Captcha" class="sr-only"><?php echo $lang->_("CAPTCHA"); ?></label>
								<input type="text" class="form-control" name="captcha" id="Captcha" placeholder="Captcha" value="" tabindex="3" /><br />
								<img src="assets/captcha/image.php" />
							</div>
							<?php } ?>
							<br/>
						
						<?php } ?>
						<div class="form-group ">				
							<button type="submit" name="reset_password" id="submit" tabindex="4" class="btn btn-lg btn-primary"><?php echo $lang->_("RESET_PASSWORD"); ?></button>
						</div>
					</form>			
				</div>
			</div>
			<center style="color: #FFF;"><?php echo $lang->_("VERSION"); ?>: <?php echo $config['version']; ?></center>
		</section>
	</article>
	</body>
</html>
<?php
$db->close();
?>