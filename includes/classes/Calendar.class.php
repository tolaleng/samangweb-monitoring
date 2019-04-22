<?php
class Calendar {
	function validateDate($date, $format = 'Y/m/d H:i') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	
	function Add($server, $information, $start_date, $end_date) {
		global $db, $lang, $login;
		
		$server = $db->real_escape_string(html($server));
		$information = $db->real_escape_string(html($information));
		$start_date = $db->real_escape_string(html($start_date));
		$end_date = $db->real_escape_string(html($end_date));
		
		$start = date("Y-m-d H:i:s", strtotime($start_date));
		$end = date("Y-m-d H:i:s", strtotime($end_date));
		
		$check_server = $db->query("SELECT * FROM servers WHERE id='{$server}' AND member_id='{$login->id}'");
		
		if(empty($server) || empty($information) || empty($start_date) || empty($end_date)) {
			throw new Exception($lang->_('FORGOT_SOMETHING'));

		} else if($check_server->num_rows == 0) {
			throw new Exception($lang->_('SERVER_NOT_FOUND'));
			
		} else if(!$this->validateDate($start_date) && !$this->validateDate($end_date)) {
			throw new Exception($lang->_('CALENDAR_ADD_INVALID_DATE'));

		} else if(date('Y/m/d H:i') >= $end_date) {
			throw new Exception($lang->_('CALENDAR_ADD_DATE_PASSED'));

		} else if($start_date >= $end_date) {
			throw new Exception($lang->_('CALENDAR_ADD_DATE_OLDER'));

		} else {
			$state = '0';
			
			if($end <= date("Y-m-d H:i:s")) {
				$state = '2';
			} else if($start <= date("Y-m-d H:i:s") && $end >= date("Y-m-d H:i:s")) {
				$state = '1';
			}
			
			$db->query("INSERT INTO calendar (member_id, server_id, information, start_date, end_date, date, state) VALUES ('{$login->id}', '{$server}', '{$information}', '{$start}', '{$end}', NOW(), '{$state}')");
		}
	}
	
		
	function Edit($id, $server, $information, $start_date, $end_date) {
		global $db, $lang, $login;
		
		$id = $db->real_escape_string(html($id));
		$server = $db->real_escape_string(html($server));
		$information = $db->real_escape_string(html($information));
		$start_date = $db->real_escape_string($start_date);
		$end_date = $db->real_escape_string($end_date);
		
		$start = date("Y-m-d H:i:s", strtotime($start_date));
		$end = date("Y-m-d H:i:s", strtotime($end_date));
		
		$check_server = $db->query("SELECT * FROM servers WHERE id='{$server}' AND member_id='{$login->id}'");
		
		$sql = $db->query("SELECT * FROM calendar WHERE id='{$id}' AND member_id='{$login->id}'");
		$row = $sql->fetch_assoc();
		
		if(empty($server) || empty($information) || empty($start_date) || empty($end_date)) {
			throw new Exception($lang->_('FORGOT_SOMETHING'));

		} else if($sql->num_rows == 0) {
			throw new Exception($lang->_('CALENDAR_NOT_FOUND'));
			
		} else if($check_server->num_rows == 0) {
			throw new Exception($lang->_('SERVER_NOT_FOUND'));
			
		} else if(!$this->validateDate($start_date) && !$this->validateDate($end_date)) {
			throw new Exception($lang->_('CALENDAR_ADD_INVALID_DATE'));

		} else if($end != $row['end_date'] && date('Y/m/d H:i') >= $end_date) {
			throw new Exception($lang->_('CALENDAR_ADD_DATE_PASSED'));

		} else if($start_date >= $end_date) {
			throw new Exception($lang->_('CALENDAR_ADD_DATE_OLDER'));

		} else {
			$state = '0';
			
			if($end <= date("Y-m-d H:i:s")) {
				$state = '2';
			} else if($start <= date("Y-m-d H:i:s") && $end >= date("Y-m-d H:i:s")) {
				$state = '1';
			}
			
			$db->query("UPDATE calendar SET server_id='{$server}', information='{$information}', start_date='{$start}', end_date='{$end}', state='{$state}' WHERE id='{$id}'") or die($db->error);
		}
	}
}
?>