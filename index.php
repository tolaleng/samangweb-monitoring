<?php
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

require_once("includes/autoload.php");

if(!$login->LoggedIn) {
	header("Location: login.php");
	die();
}
if(isset($_GET['signout'])) {
	$hash = $cookie->Get("LoggedIn_Token");
	$db->query("DELETE FROM sessions WHERE hash='{$hash}'");
	$cookie->Delete("LoggedIn_Token");
	header("Location: login.php");
	die();
}

if(isset($_GET["phpinfo"]) && $login->Access()) {
	if(DEMO == 0) {
		phpinfo();
		die();
	} else {
		die("Disabled in the demo");
	}
} else if(isset($_GET["download_export"]) && $login->LoggedIn) {
	$file = "_tmp/" . $_GET["download_export"];
	if(file_exists($file)) { 
		header('Content-Description: File Transfer');
		header('Content-Type: ' . mime_content_type($file));
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		
		readfile($file);
	} else {
		echo "This file does not exists.";
	}
	die();
}

$lang->setLang($login->lang);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<meta name="author" content="Justin991q">

	<title><?php echo $config['name']; ?></title>

	<link rel="icon" href="assets/images/icon.png">
	
	<link href="assets/css/<?php echo $function->Theme($login->Theme, "css"); ?>" rel="stylesheet">
	<link href="assets/css/jumbotron-narrow.css" rel="stylesheet">
	<link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
	<link href="assets/datetimepicker/jquery.datetimepicker.css" rel="stylesheet">

	<script type="text/javascript" src="assets/js/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/jquery.knob.min.js"></script>
	<script type="text/javascript" src="assets/datetimepicker/jquery.datetimepicker.min.js"></script>
	<script src="https://code.highcharts.com/highcharts.js"></script>

	<script type="text/javascript">
		$(function () {
			$('[data-toggle="tooltip"]').tooltip()
			$(".dial").knob();
			$('#start_date').datetimepicker({ startDate: '<?php echo date("Y/m/d"); ?>', minDate: '<?php echo date("Y/m/d"); ?>', mask:true });
			$('#end_date').datetimepicker({ startDate: '<?php echo date("Y/m/d"); ?>', minDate: '<?php echo date("Y/m/d"); ?>', mask:true });
		});
	</script>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>
<script type="text/javascript">
function LoadNotification() {
	$( "#Desktop_Notification" ).load( "ajax/Notification.php" );
}

$( window ).load(function() {
	LoadNotification();
	setInterval(function() {
		LoadNotification();
	}, 50000);
});
</script>

<div id="Desktop_Notification"></div>

<div class="container">
	<div class="header clearfix">
		<nav role="navigation">
			<ul class="nav nav-pills pull-right">
				<li class="<?php echo $function->Active_Page("/").$function->Active_Page("dashboard"); ?>"><a href="index.php"><?php echo $lang->_('MENU_DASHBOARD'); ?></a></li>
				<li class="<?php echo $function->Active_Page("server_manager"); ?>"><a href="index.php?p=server_manager"><?php echo $lang->_('SERVER_MANAGER'); ?></a></li>
				<li class="<?php echo $function->Active_Page("history"); ?>"><a href="index.php?p=history"><?php echo $lang->_('HISTORY'); ?></a></li>
				<li class="<?php echo $function->Active_Page("pushbullet"); ?>"><a href="index.php?p=pushbullet">Pushbullet</a></li>
				
				<li class="dropdown">
					<a id="drop5" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<?php echo $lang->_('HELP'); ?>
						<span class="caret"></span>
					</a>
					<ul id="menu2" class="dropdown-menu" aria-labelledby="drop5">
						<li><a href="index.php?p=widget"><?php echo $lang->_('WIDGETS'); ?></a></li>
						<li><a href="index.php?p=version"><?php echo $lang->_('VERSION'); ?></a></li>
					</ul>
				</li>
				<?php
					if($login->Access()) {
				?>
					<li class="dropdown">
						<a id="drop5" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
							<font color="red"><?php echo $lang->_('ADMINISTRATION'); ?> <span class="caret"></span></font>
						</a>
						<ul id="menu2" class="dropdown-menu" aria-labelledby="drop5">
							<li><a href="index.php?p=admin/users"><?php echo $lang->_('USER_MANAGEMENT'); ?></a></li>
							<li><a href="index.php?p=admin/config"><?php echo $lang->_('CONFIG'); ?></a></li>
							<li><a href="index.php?p=admin/response_code"><?php echo $lang->_('RESPONSE_CODES'); ?></a></li>
							<li><a href="index.php?p=admin/language"><?php echo $lang->_('LANGUAGE_MANAGER'); ?></a></li>
							<li><a href="index.php?p=admin/system"><?php echo $lang->_('SYSTEM_CHECK'); ?> <i class="<?php echo $system->DoCheck(true); ?>"></i></a></li>
						</ul>
					</li>
			<?php }	?>
				<li class="dropdown">
					<a id="drop5" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<?php echo html($login->username); ?>
						<span class="caret"></span>
					</a>
					<ul id="menu2" class="dropdown-menu" aria-labelledby="drop5">
						<li><a href="index.php?p=account_settings"><?php echo $lang->_('ACCOUNT_SETTINGS'); ?></a></li>
						<li><a href="index.php?signout"><?php echo $lang->_('SIGNOUT'); ?></a></li>
					</ul>
				</li>
			</ul>
		</nav>
		<h3 class="text-muted"><?php echo $config['name']; ?></h3>
	</div>
	<?php
		if(DEMO == 1) {
			echo '<div class="alert alert-danger">'.$lang->_('DEMO').'</div>';
		}
		include("includes/pagesystem.php");
	?>

	<footer class="footer">
		<p>&copy; <?php echo $config['name']; ?> <?php echo (date('Y') - 1)." - ".date('Y'); ?>. <?php echo $lang->_('VERSION'); ?>: <?php echo $config['version']; ?></p>
	</footer>
</div>
</body>
</html>
<?php
$db->close();
?>