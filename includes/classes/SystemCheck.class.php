<?php
class SystemCheck {
	function DoCheck($show_icon = false) {
		global $config, $lang;
		$points = 0;
		$max_points = 11;
		$files = array(
			"pages/config.php",
			"pages/response_code.php",
			"pages/users.php",
			"includes/cert/ca-bundle.crt",
			"signin.php",
		);
		
		$folders = array(
			"install",
			"update",
			"includes/cert",
		);
		
		$file_error = "";
		foreach($files as $file) {
			if(file_exists($file)) {
				$file_error .= "<div class='alert alert-danger'>{$lang->_('SYSTEM_CHECK_FILE_EXIST', array("%file%" => $file))}</div>";
				$points = $points + 1;
			}
		}
		
		$folders_error = "";
		foreach($folders as $folder) {
			if(is_dir($folder)) {
				$folders_error .= "<div class='alert alert-danger'>{$lang->_('SYSTEM_CHECK_FOLDER_EXIST', array("%folder%" => $folder))}</div>";
				$points = $points + 1;
			}
		}
		
		$cron_error = "";
		if(date("Y-m-d H:i") > date("Y-m-d H:i", strtotime($config['last_cron'] . " +5 MINUTE"))) {
			$cron_error .= "<div class='alert alert-danger'>{$lang->_('SYSTEM_CHECK_LAST_CRON_RUN', array("%date%" => $config['last_cron']))}</div>";
			$points = $points + 1;
		}
		
		$chmod_error = "";
		if(substr(sprintf('%o', fileperms('config.php')), -4) != "0644") {
			$chmod_error .= "<div class='alert alert-danger'>{$lang->_('SYSTEM_CHECK_FILE_WRITEABLE', array("%file%" => "config.php"))}</div>";
			$points = $points + 1;
		}
		
		$chmod_error_1 = "";
		if(!is_writeable("_tmp")) {
			$chmod_error .= "<div class='alert alert-danger'>{$lang->_('SYSTEM_CHECK_FOLDER_NOT_WRITEABLE', array("%folder%" => "_tmp"))}</div>";
			$points = $points + 1;
		}
		
		
		$procent = round(100 / $max_points * $points, 0);
		if($show_icon == false) {
			if($points == 0) {
				return "<div class='alert alert-success'>{$lang->_('SYSTEM_CHECK_NO_ERRORS')}</div>";
			} else {
				return $file_error.$folders_error.$cron_error.$chmod_error;
			}
		} else {
			if($procent == 0) {
				return "fa fa-check text-success";
			} else {
				return "fa fa-exclamation-triangle text-danger";
			}
		}
	}
}
?>