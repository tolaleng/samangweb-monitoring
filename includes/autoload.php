<?php
$current_version = "1.4.4";

function __autoload($class) {
	if(file_exists(dirname(__FILE__) . "/classes/{$class}.class.php")) {
		require_once(dirname(__FILE__) . "/classes/{$class}.class.php");

	} else if(file_exists(dirname(__FILE__) . "/classes/PHPMailer/class.{$class}.php")) {
		require_once(dirname(__FILE__) . "/classes/PHPMailer/class.{$class}.php");

	}
}

require_once(dirname(__FILE__) . "/../config.php");
$function = new Functions;

if($function->IsInstalled() == 1) {
	header("Location: install");
}

require_once(dirname(__FILE__) . "/database.php");
if($function->IsUpdated($config['version'], $current_version) == 1) {
	if(stripos($_SERVER['REQUEST_URI'], "/update/") === false) {
		header("Location: update");
	}
}

$lang = new Language;
$csrf = new CSRF;
$cookie = new Cookie;
$mail = new Email;
$login = new Login;
$server = new Server;
$pb = new PBPage;
$system = new SystemCheck;
?>