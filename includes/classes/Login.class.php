<?php
class LoginError extends Exception { }

class Login {
	public $LoggedIn = false;
	public $username = false;
	public $email = false;
	public $id = false;
	public $Theme = "light";
	public $lang = "en";
	
	function __construct() {
		global $db, $cookie, $config;
		
		if($cookie->Exists("LoggedIn_Token") && !$cookie->IsEmpty("LoggedIn_Token")) {
			$token = $db->real_escape_string(html($cookie->Get("LoggedIn_Token")));
			
			$sql = $db->query("SELECT * FROM sessions WHERE hash='{$token}' AND ip='{$_SERVER['REMOTE_ADDR']}'");
			$row = $sql->fetch_assoc();
			if($sql->num_rows == 1) {
				$usercheck = $db->query("SELECT * FROM users WHERE id='{$row['member_id']}'");
				$usercheck_row = $usercheck->fetch_assoc();
				if($usercheck->num_rows == 1) {
					$this->LoggedIn = true;
					
					$this->id = $row['member_id'];
					$this->username = $usercheck_row['username'];
					$this->email = $usercheck_row['email'];

					if(isset($usercheck_row['theme'])) {
						$this->Theme = $usercheck_row['theme'];
					} else {
						$this->Theme = "light";
					}
					
					if(isset($usercheck_row['language'])) {
						if($usercheck_row['language'] == "") {
							$this->lang = $config['default_language'];
						} else {
							$this->lang = $usercheck_row['language'];							
						}
					} else {
						$this->lang = "en";
					}
				} else {
					$cookie->Delete("LoggedIn_Token");
				}
			} else {
				$cookie->Delete("LoggedIn_Token");
			}
		}
	}
	
	function SignIn($username, $password, $captcha) {
		global $db, $config, $lang;
		
		$username = $db->real_escape_string(html($username));
		$password = $db->real_escape_string(hash("whirlpool", $password));
		$captcha = $db->real_escape_string(html($captcha));
		
		if(empty($username) && empty($password)) {
			throw new LoginError($lang->_('FIELDSREQUIRED'));
		} else if($config['captcha'] == "1" && $_SESSION['captcha'] != hash("sha256", $captcha)) {
			throw new LoginError($lang->_('WRONGCAPTCHA'));
		} else {
			$sql = $db->query("SELECT * FROM users WHERE (username='{$username}' OR email='{$username}') AND password='{$password}'");
			$row = $sql->fetch_assoc();
			if($sql->num_rows == 0) {
				throw new LoginError($lang->_('USERNAME_PASSWORD_NOT_CORRECT'));
			} else if($row['active'] == "0") {
				throw new LoginError($lang->_('ACCOUNT_INACTIVE'));
			} else {
				unset($_SESSION['captcha']);
				$this->CreateSession($row['id']);
			}
		}
	}
	
	function CreateSession($member_id) {
		global $db, $cookie;
		
		$id = $db->real_escape_string(html($member_id));
		
		$sql = $db->query("SELECT * FROM users WHERE id='{$id}'");
		$row = $sql->fetch_assoc();
		
		if($sql->num_rows == 0) {
			throw new LoginError("Monitor Error: #100");
		} else {
			$uniqid = uniqid("Monitor-");
			$hash = hash("whirlpool", "{$uniqid}{$row['username']}{$row['email']}");
			
			$cookie->Set("LoggedIn_Token", $hash);
			
			$db->query("UPDATE users SET last_signin=NOW() WHERE id='{$id}'");
			$db->query("INSERT INTO sessions (member_id, date, hash, ip) VALUES ('{$id}', NOW(), '{$hash}', '{$_SERVER['REMOTE_ADDR']}')");
		}
	}
	
	function Access() {
		global $db, $config;
		
		if($this->LoggedIn) {
			$user = $db->query("SELECT * FROM users WHERE id='{$this->id}'");
			$user = $user->fetch_assoc();
			if($user['admin'] == "1") {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function AddUser($username, $email, $password, $password1, $theme='light', $max_servers="-1", $access="0") {
		global $db, $config, $function, $lang, $mail;
		
		$username = $db->real_escape_string(html($username));
		$email = $db->real_escape_string(html($email));
		$password = $db->real_escape_string(html($password));
		$password1 = $db->real_escape_string(html($password1));
		$theme = $db->real_escape_string(html($theme));
		$max_servers = $db->real_escape_string(html($max_servers));
		$access = $db->real_escape_string(html($access));
		
		if($max_servers == "-1") {
			$max_servers = $config['default_max_servers'];
		}
		
		$active = '1';
		if($config['user_activate'] == "1") {
			$active = '0';
		}
		
		$check_user = $db->query("SELECT * FROM users WHERE username='{$username}'");
		$check_user_row = $check_user->fetch_assoc();
		
		$email_check = $db->query("SELECT * FROM users WHERE email='{$email}'")->num_rows;
		
		$theme_exist = $function->Theme($theme, "css");
		
		if(empty($username) && empty($email) && empty($password) && empty($password1) && empty($theme) && empty($access)) {
			throw new LoginError($lang->_('FIELDSREQUIRED'));
			
		} else if(strlen($username) < 4) {
			throw new LoginError($lang->_("SIGNUP_LONGER_USERNAME", array("%char%" => 4)));
			
		} else if(strlen($password) < 6) {
			throw new LoginError($lang->_("SIGNUP_LONGER_PASSWORD", array("%char%" => 6)));
			
		} else if($password != $password1) {
			throw new LoginError($lang->_('SIGNUP_PASSWORD_NOT_MATCH'));
			
		} else if($check_user->num_rows != 0) {
			throw new LoginError($lang->_('SIRNUP_USERNAME_EXIST'));
			
		} else if(!$function->ValidateEmail($email)) {
			throw new LoginError($lang->_('SIGNUP_EMAIL_INVALID'));
			
		} else if($email_check != 0) {
			throw new LoginError($lang->_('SIRNUP_EMAIL_EXIST'));
			
		} else if(empty($theme_exist)) {
			throw new LoginError($lang->_('SIGNUP_THEME_INVALID'));
			
		} else if(!is_numeric($max_servers)) {
			throw new LoginError($lang->_('SIGNUP_MAXSERVER_INTEGER'));
			
		} else {
			if(DEMO == 0) {
				$db->query("INSERT INTO users (username, password, email, max_servers, theme, admin, active) VALUES ('{$username}', '".hash("whirlpool", $password)."', '{$email}', '{$max_servers}', '{$theme}', '{$access}', '{$active}')");
				$member_id = $db->insert_id;
				
				if($config['user_activate'] == "1") {
					$code = md5(uniqid());
					$url = $function->URL(1) . "login.php?activate={$code}";
					$message = $lang->_('ACCOUNT_ACTIVATE_EMAIL_MESSAGE', array("%title%" => $config['name'], "%url%" => $url, "%username%" => $username, "%password%" => $password));
					$mail->Send($email, $lang->_('ACCOUNT_ACTIVATE_EMAIL_TITLE'), $message);
		
					$db->query("INSERT INTO activate (member_id, code, date) VALUES ('{$member_id}', '{$code}', NOW())");
				}
			}

		}
	}
	
	function EditUser($id, $email, $password, $password1, $chart1=0, $chart2=0, $theme=0, $language='', $access=0, $max_servers=0) {
		global $db, $config, $function, $lang;
		
		$id = $db->real_escape_string(html($id));
		$email = $db->real_escape_string(html($email));
		$password = $db->real_escape_string(html($password));
		$password1 = $db->real_escape_string(html($password1));
		$chart1 = $db->real_escape_string(html($chart1));
		$chart2 = $db->real_escape_string(html($chart2));
		$theme = $db->real_escape_string(html($theme));
		$language = $db->real_escape_string(html($language));
		$access = $db->real_escape_string(html($access));
		$max_servers = $db->real_escape_string(html($max_servers));
		
		$check_user = $db->query("SELECT * FROM users WHERE id='{$id}'");
		$check_user_row = $check_user->fetch_assoc();
		
		$chart1_check = $db->query("SELECT * FROM servers WHERE id='{$chart1}' AND member_id='{$id}' AND disabled='0' AND deleted='0'");
		$chart2_check = $db->query("SELECT * FROM servers WHERE id='{$chart2}' AND member_id='{$id}' AND disabled='0' AND deleted='0'");
		
		$email_check = $db->query("SELECT * FROM users WHERE email='{$email}'")->num_rows;
		
		$theme_exist = $function->Theme($theme, "css");
		
		if($check_user->num_rows == 0) {
			throw new LoginError($lang->_('USER_NOT_FOUND'));
			
		} else if(!empty($password) && $password != $password1) {
			throw new LoginError($lang->_('SIGNUP_PASSWORD_NOT_MATCH'));
			
		} else if(!empty($password) && strlen($password) < 6) {
			throw new LoginError($lang->_("SIGNUP_LONGER_PASSWORD", array("%char%" => 6)));
			
		} else if($chart1 != "0" && $chart1_check->num_rows == 0) {
			throw new LoginError("Dashboard chart 1 was not found.");

		} else if($chart2 != "0" && $chart2_check->num_rows == 0) {
			throw new LoginError("Dashboard chart 2 was not found.");
			
		} else if($check_user_row['email'] != $email && !$function->ValidateEmail($email)) {
			throw new LoginError($lang->_('SIGNUP_EMAIL_INVALID'));
			
		} else if($check_user_row['email'] != $email && $email_check != 0) {
			throw new LoginError($lang->_('SIRNUP_EMAIL_EXIST'));
			
		} else if(empty($theme_exist)) {
			throw new LoginError($lang->_('SIGNUP_THEME_INVALID'));
			
		} else if(!is_numeric($max_servers)) {
			throw new LoginError($lang->_('SIGNUP_MAXSERVER_INTEGER'));
			
		} else if($language != "default" && !file_exists("includes/languages/{$language}.txt")) {
			throw new LoginError($lang->_('LANGUAGE_NOT_FOUND'));
		} else {
			if($language == "default") { $language = ""; }
			if(DEMO == 0) {
				$db->query("UPDATE users SET admin='{$access}', chart_1='{$chart1}', chart_2='{$chart2}', theme='{$theme}', max_servers='{$max_servers}', language='{$language}' WHERE id='{$id}'");
				if(!empty($password)) {
					$db->query("UPDATE users SET password='".hash("whirlpool", $password)."' WHERE id='{$id}'");
				}
				if($check_user_row['email'] != $email) {
					$db->query("UPDATE users SET email='{$email}' WHERE id='{$id}'");				
				}
			}
			
		}
	}
}
?>