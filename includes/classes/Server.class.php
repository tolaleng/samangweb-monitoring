<?php
class ServerError extends Exception { }

class Server {
	public function Color($id) {
		global $lang;
		if($id == 'deleted') {
			return '<div class="label label-danger">'.$lang->_('DELETED').'</div>';
		} else if($id == 'disabled') {
			return '<div class="label label-default">'.$lang->_('DISABLED').'</div>';
		} else if($id == 'down') {
			return '<div class="label label-danger"><i class="fa fa-arrow-down"></i> '.$lang->_('OFFLINE').'</div>';
		} else if($id == 'active') {
			return '<div class="label label-success"><i class="fa fa-arrow-up"></i> '.$lang->_('ONLINE').'</div>';
		} else if($id == 'unknown') {
			return '<div class="label label-warning">'.$lang->_('UNKNOWN').'</div>';
		}
	}
	
	public function ProgressColor($value) {
		if($value < 30) {
			return "success";
		} else if($value < 75) {
			return "warning";
		} else if($value < 100) {
			return "danger";
		} else {
			return "danger";
		}
	}
	
	public function Uptime($value) {
		if($value < 30) {
			return "danger";
		} else if($value < 75) {
			return "warning";
		} else if($value < 100) {
			return "success";
		} else {
			return "success";
		}
	}
	
	public function Add($disp, $url, $email, $enabled="0", $widget="0", $desktop="0", $pushbullet="0", $timeout, $check_time) {
		global $db, $pb, $login, $lang, $function;
		
		$disp = $db->real_escape_string(html($disp));
		$url = $db->real_escape_string(html($url));
		$email = $db->real_escape_string(html($email));
		$enabled = $db->real_escape_string(html($enabled));
		$widget = $db->real_escape_string(html($widget));
		$desktop = $db->real_escape_string(html($desktop));
		$pushbullet = $db->real_escape_string(html($pushbullet));
		$timeout = $db->real_escape_string(html($timeout));
		$check_time = $db->real_escape_string(html($check_time));
		
		
		$check_exists = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}' AND server_url='{$url}'");

		if(empty($url) && empty($disp)) {
			throw new ServerError($lang->_('FORGOT_URL_NAME'));
			
		} else if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			throw new ServerError($lang->_('VALID_SERVER_URL'));
			
		} else if(!empty($email) && !$function->ValidateEmail($email)){ 
			throw new ServerError($lang->_('SIGNUP_EMAIL_INVALID'));
			
		} else if($check_exists->num_rows != 0) {
			throw new ServerError($lang->_('DOMAIN_EXISTS'));
			
		} else if($pushbullet != "0" && !$pb->CheckExists($pushbullet, $login->id)){ 
			throw new ServerError($lang->_('PUSHBULLET_NOT_FOUND'));
			
		} else if(preg_match("/^[0-9]+$/", $check_time) == 0) {
			throw new ServerError($lang->_('INVALID_CHECK_TIME'));
			
		} else if(preg_match("/^[0-9]+$/", $timeout) == 0) {
			throw new ServerError($lang->_('INVALID_TIMEOUT_TIME'));
			
		} else {
			if($check_time == 0) { $check_time = 1; }
			if(DEMO == 0) {
				$db->query("INSERT INTO servers (member_id,display_name,server_url,email_to,disabled,widget,desktop_notif,pushbullet,back_online,check_time,timeout) VALUES ('{$login->id}', '{$disp}', '{$url}', '{$email}', '{$enabled}', '{$widget}', '{$desktop}', '{$pushbullet}',NOW(),'{$check_time}', '{$timeout}')");
			}
		}
	}
	
	public function Edit($id, $disp, $url, $email, $enabled="0", $widget="0", $desktop="0", $pushbullet="0", $owner="0", $timeout="0", $check_time='1') {
		global $db, $pb, $lang, $function, $login;
		
		$disp = $db->real_escape_string(html($disp));
		$url = $db->real_escape_string(html($url));
		$email = $db->real_escape_string(html($email));
		$enabled = $db->real_escape_string(html($enabled));
		$widget = $db->real_escape_string(html($widget));
		$desktop = $db->real_escape_string(html($desktop));
		$pushbullet = $db->real_escape_string(html($pushbullet));
		$owner = $db->real_escape_string(html($owner));
		$timeout = $db->real_escape_string(html($timeout));
		$check_time = $db->real_escape_string(html($check_time));
		
		$check_user = $db->query("SELECT * FROM users WHERE id='{$owner}'")->num_rows;
		$server_check = $db->query("SELECT * FROM servers WHERE id='{$id}'")->fetch_assoc();
		
		if($server_check['member_id'] != $owner) { $pushbullet = 0; }
		
		$check_exists = $db->query("SELECT * FROM servers WHERE member_id='{$login->id}' AND server_url='{$url}'");

		if(empty($url) && empty($disp)) {
			throw new ServerError($lang->_('FORGOT_URL_NAME'));
			
		} else if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			throw new ServerError($lang->_('VALID_SERVER_URL'));
			
		} else if($server_check['server_url'] != $url && $check_exists->num_rows != 0) {
			throw new ServerError($lang->_('DOMAIN_EXISTS'));
			
		} else if(!empty($email) && !$function->ValidateEmail($email)){ 
			throw new ServerError($lang->_('SIGNUP_EMAIL_INVALID'));
			
		} else if($pushbullet != "0" && !$pb->CheckExists($pushbullet, $owner)){ 
			throw new ServerError($lang->_('PUSHBULLET_NOT_FOUND'));
			
		} else if($check_user == 0){ 
			throw new ServerError($lang->_('USER_NOT_FOUND'));
			
		} else if(preg_match("/^[0-9]+$/", $check_time) == 0) {
			throw new ServerError($lang->_('INVALID_CHECK_TIME'));
			
		} else if(preg_match("/^[0-9]+$/", $timeout) == 0) {
			throw new ServerError($lang->_('INVALID_TIMEOUT_TIME'));
			
		} else {
			if($check_time == 0) { $check_time = 1; }
			if(DEMO == 0) {
				$db->query("UPDATE servers SET member_id='{$owner}', display_name='{$disp}', server_url='{$url}', email_to='{$email}', disabled='{$enabled}', widget='{$widget}', desktop_notif='{$desktop}', pushbullet='{$pushbullet}', check_time='{$check_time}', timeout='{$timeout}' WHERE id='{$id}'");
				$db->query("UPDATE server_stats SET member_id='{$owner}' WHERE server_id='{$id}'");
				$db->query("UPDATE server_events SET member_id='{$owner}' WHERE server_id='{$id}'");
				$db->query("UPDATE history SET member_id='{$owner}' WHERE server_id='{$id}'");
			}
		}
	}
	
	public function GetLastStats($id) {
		global $db;
		
		$id = $db->real_escape_string(html($id));
		if(empty($id)) {
			return "ID was not found!";
		} else {
			$query = $db->query("SELECT * FROM servers WHERE id='{$id}'");
			$query = $query->fetch_assoc();
			return $query;
		}
	}
	
	public function ResponseName($code = NULL) {

		if ($code !== NULL) {
			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 306: $text = 'Switch Proxy'; break;
				case 307: $text = 'Temporary Redirect'; break;
				case 308: $text = 'Permanent Redirect'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:  $text = 'Unknown'; break;
			}
			return $text;
		}
	}
	
	public function ResponseOnline($name, $mysql=0) {
		global $db;
		
		if($mysql == 1) {
			$sql = $db->query("SELECT * FROM response_codes ORDER BY code ASC");
			
			$name = $db->real_escape_string($name);
			
			$i = 1;
			$values = '(';
			while($row = $sql->fetch_assoc()) {
				$values .= "{$name}='{$row['code']}'" . (($i!=$sql->num_rows) ? " OR " : "");
				$i++;
			}
			$values .= ")";
			return $values;
		} else {
			$sql = $db->query("SELECT * FROM response_codes WHERE code='{$name}' ORDER BY code ASC");
			if($sql->num_rows == 0) {
				return false;
			}
			return true;
		}
	}
	
	public function LogEvent($server_id, $member_id, $state, $response_code, $curl_error) {
		global $db;
		
		$sql = $db->query("SELECT * FROM server_events WHERE server_id='{$server_id}' ORDER BY date DESC LIMIT 1");
		$row = $sql->fetch_assoc();
		
		if($sql->num_rows == 0) {
			$db->query("INSERT INTO server_events (server_id, member_id, state, response_code, curl_error, date) VALUES ('{$server_id}','{$member_id}','{$state}','{$response_code}','{$curl_error}', NOW())");
		} else if($row['state'] != $state) {
			$db->query("INSERT INTO server_events (server_id, member_id, state, response_code, curl_error, date) VALUES ('{$server_id}','{$member_id}','{$state}','{$response_code}','{$curl_error}', NOW())");
		}
	}
}
?>