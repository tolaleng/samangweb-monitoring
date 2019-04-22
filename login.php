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
$username = "";

$form_names = array(
					"username" => array("required" => 1),
					"password" => array("required" => 1)
				);

if(isset($_POST['log-me-in'])) {
	$username = ((!empty($_POST['username'])) ? $_POST['username'] : "");
	$email = ((!empty($_POST['email'])) ? $_POST['email'] : "");
	if($csrf->CheckForms($form_names)) {
		if($csrf->CheckToken()) {
			try {
				$captcha = (isset($_POST['captcha']) ? $_POST['captcha'] : '');
				$login->SignIn($_POST['username'], $_POST['password'], $captcha);
				header("Location: index.php");
			}
			catch(LoginError $e) {
				$output = '<div class="alert alert-danger">'.$e->getMessage().'</div>';
			}
		}else{
			$output = '<div class="alert alert-danger">'.$lang->_('INVALIDCRFS').'</div>';
		}
	}else{
		$output = '<div class="alert alert-danger">'.$lang->_('FIELDSREQUIRED').'</div>';
	}
} else if(isset($_GET['activate'])) {
	$code = $db->real_escape_string(html($_GET['activate']));
	
	$sql = $db->query("SELECT * FROM activate WHERE code='{$code}'");
	$row = $sql->fetch_assoc();
	
	if($sql->num_rows == 0) {
		$output = '<div class="alert alert-danger">'.$lang->_('INVALID_ACTIVATE_CODE').'</div>';
	} else {
		$db->query("UPDATE users SET active='1' WHERE id='{$row['member_id']}'");
		$db->query("DELETE FROM activate WHERE code='{$code}'");
		$output = '<div class="alert alert-success">'.$lang->_('ACCOUNT_ACTIVATED').'</div>';
	}
	echo $code;
	
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
	
	
		<title><?php echo $config['name']; ?> - <?php echo $lang->_("SIGNIN"); ?></title>

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
				<li class="active"><a href="login.php"><?php echo $lang->_("SIGNIN"); ?></a></li>
				<li><a href="reset_password.php"><?php echo $lang->_("RESET_PASSWORD"); ?></a></li>
				<?php if($config['register'] == "1") { ?><li><a href="register.php"><?php echo $lang->_("SIGNUP"); ?></a></li><?php } ?>
			</ul>
			<div class="tab-content tabs-login col-lg-12 col-md-12 col-sm-12 cols-xs-12">
				<div id="login-access" class="tab-pane fade active in">
					<h2><i class="glyphicon glyphicon-log-in"></i> <?php echo $lang->_("SIGNIN"); ?></h2>
					<?php echo $output; ?>	

					<?php if(DEMO == 1) { ?>
						<div class="row">
							<div class="col-md-6">
								<button class="btn btn-success" onclick="$('#login_value').val('admin');$('#password').val('admin');">Admin</button>
							</div>
							<div class="col-md-6">
								<button class="btn btn-success" onclick="$('#login_value').val('user');$('#password').val('user');">User</button>
							</div>
						</div><br /><br />
					<?php } ?>
					
					<form method="post" accept-charset="utf-8" autocomplete="off" role="form" class="form-horizontal">
						<?php echo $csrf->CreateInput(); ?>
						<div class="form-group ">
							<input type="text" class="form-control" name="username" id="login_value" placeholder="<?php echo $lang->_("USERNAME_EMAIL"); ?>" tabindex="1" value="<?php echo $username; ?>" autofocus />
						</div>
						<div class="form-group ">
							<input type="password" class="form-control" name="password" id="password" placeholder="<?php echo $lang->_("PASSWORD"); ?>" value="" tabindex="2" />
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
						<div class="form-group ">				
							<button type="submit" name="log-me-in" id="submit" tabindex="4" class="btn btn-lg btn-primary"><?php echo $lang->_("SIGNIN"); ?></button>
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