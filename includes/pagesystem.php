<?php
$pages = array(
	/* Users */
	"dashboard",
	"server_manager",
	"version",
	"account_settings",
	"pushbullet",
	"widget",
	"history",
	"calendar",
	
	/* Admin */
	"admin/response_code",
	"admin/users",
	"admin/config",
	"admin/mail_settings",
	"admin/system",
	"admin/language",
);


if(isset($_GET['p'])) {
    if(in_array($_GET['p'], $pages)) {
        if(file_exists("pages/{$_GET['p']}.php") == false) {
           include("pages/dashboard.php");
        }else{
            include("pages/{$_GET['p']}.php");
        }
    }else{
        include("pages/dashboard.php");
    }
}else{
    include("pages/dashboard.php");
}
?>