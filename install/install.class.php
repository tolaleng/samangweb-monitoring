<?php
class Install {
	public function Errors() {
		if(phpversion() <= "5.2.0") {
			return false;
		}elseif(!is_writable(dirname(__FILE__)."/../config.php")) {
			return false;
		}elseif(!is_writable(dirname(__FILE__)."/../_tmp")) {
			return false;
		}elseif(!function_exists("curl_init")) {
			return false;
		}elseif(!function_exists("mysqli_connect")) {
			return false;
		}
		return true;
	}
	
	public function Steps($step='1', $check='0') {
		global $ds;
		if(isset($_SESSION['step'])) {
			if($_SESSION['step'] == 5) {
				if($check == "1") {
					return "<span class='fa fa-check'></span>";
				}else{
					return "active";
				}
			}elseif($_SESSION['step'] == $step) {
				if($check == "0") { return "active"; } 
			}elseif($_SESSION['step'] >= $step) {
				if($check == "1") {
					return "<span class='fa fa-check'></span>";
				}else{
					return "active";
				}
			}
		}elseif($step == '1') {
			return "active";
		}else{
			return "";
		}
	}
	
	public function html($string) {
		return htmlentities(stripslashes($string), ENT_QUOTES, "UTF-8");
	}
}
$install = new Install;
?>