<?php
class Functions {
	public function Active_Page($pagename, $classname='active') {
		if(isset($_GET['p'])) {
			if($_GET['p'] == $pagename) {
				return $classname;   
			}
		}elseif($pagename == "/") {
			return $classname;
		}
	}

	public function Redirect($url, $timeout=2) {
		return '<meta http-equiv="refresh" content="'.$timeout.'; url='.$url.'">';
		
	}

	public function Tooltip($title, $pos='top') {
		return 'data-toggle="tooltip" data-placement="'.$pos.'" title="'.$title.'"';
		
	}

	public function Procent($totaal,$deel){
		if($totaal!="0"){
			$percent=100*($deel/$totaal);
			if($percent > 100) {
				return 100;
			}else{
				return round($percent, 2);
			}
		}
	}

	public function Uptime($succeed, $failed){
		if($succeed != 0){
			if($failed > $succeed) {
				$percent = 100*($succeed/$failed);
				if($percent > 100) {
					return 100;
				}else{
					return round($percent, 2);
				}
			} else {
				$percent = 100*($failed/$succeed);
				if($percent > 100) {
					return 100;
				}else{
					return 100 - round($percent, 2);
				}
			}
		} else {
			return 0;
		}
	}
	
	public function IsInstalled() {
		if(is_dir(dirname(__FILE__)."/../../install")) {
			if(DB_USERNAME == "<db_username>") {
				return 1;
			}else{
				return 2;
			}
		}else{
			return 0;
		}
	}
	
	public function IsUpdated($db_version, $version) {
		if(is_dir(dirname(__FILE__)."/../../update")) {
			if($db_version != $version) {
				return 1;
			}else{
				return 2;
			}
		}else{
			return 0;
		}
	}
	
	public function Theme($color, $option) { 
		global $config;
		
		$colors = array(
			"light" => array("css" => "bootstrap.min.css", "knob" => "", "ChartBG" => "FFF", "ChartFont" => "000"),
			"dark" => array("css" => "bootstrap.dark.min.css", "knob" => "'bgColor': '#3D3D3D'", "ChartBG" => "222", "ChartFont" => "FFF"),
		);
		if(isset($colors[$color][$option])) {
			return $colors[$color][$option];
		} else {
			return "";
		}
	}
	
	function URL($file = 0) {
		$ssl = 0;
		
		$link = "/";
		
		if($file == 0) {
			if($link == "/") {
				$link = $_SERVER['REQUEST_URI'];
			} else {
				$link = substr($link, 1)."/";
			}
		} else {
			$curdir = $_SERVER['REQUEST_URI']."/";
			$curdir = explode("/", $curdir);
			$total = count($curdir) - 2;
			
			$i = 0;
			foreach($curdir as $folder) {
				if(!empty($folder) && $folder != $curdir[$total]) {
					$link .= $folder. "/";
				}
				
				$i++;
			}
			
		}
		
		return (($ssl == 1) ? "https://" : "http://").$_SERVER['HTTP_HOST'].$link;
	}
	
	function ValidateEmail($email) {
		if(!preg_match('/^[a-zA-Z0-9.@\-_]+$/i', $email)) {
			return false;
			
		} else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
			
		} else {
			return true;
		}
	}
	
	function pr($text) {
		return "<pre><b>Debug Information:</b><br />" . print_r($text, true) . "</pre>";
	}
	
	function FileMaxSize($parse = 1) {
		$max_size = -1;
		
		if ($max_size < 0) {
			if($parse == 1) {
				$max_size = $this->ParseSize(ini_get('post_max_size'));
				$upload_max = $this->ParseSize(ini_get('upload_max_filesize'));
			}else{
				$max_size = ini_get('post_max_size');
				$upload_max = ini_get('upload_max_filesize');
			}
			
			if ($upload_max > 0 && $upload_max < $max_size) {
				$max_size = $upload_max;
			}
		}
		return $max_size;
	}
	
	function ParseSize($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
		$size = preg_replace('/[^0-9\.]/', '', $size);
		if ($unit) {
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}else{
			return round($size);
		}
	}
	
	function dateDiff($time1, $time2) {
		if (!is_int($time1)) {
			$time1 = strtotime($time1);
		}
		if (!is_int($time2)) {
			$time2 = strtotime($time2);
		}

		if ($time1 > $time2) {
			$ttime = $time1;
			$time1 = $time2;
			$time2 = $ttime;
		}

		$intervals = array('day','hour','minute');
		$diffs = array();

		foreach ($intervals as $interval) {
			$ttime = strtotime('+1 ' . $interval, $time1);
			$add = 1;
			$looped = 0;
			while ($time2 >= $ttime) {
				$add++;
				$ttime = strtotime("+" . $add . " " . $interval, $time1);
				$looped++;
			}

			$time1 = strtotime("+" . $looped . " " . $interval, $time1);
			$diffs[$interval] = $looped;
		}
		
		$count = 0;
		$times = array();
		foreach ($diffs as $interval => $value) {
			if ($value > 0) {
				if ($value != 1) {
					$interval .= "s";
				}
				$times[] = $value;
				$count++;
			}
		}
		return $times;
	}
}
?>