<?php
class Language {
	private $lang;
	
	function __construct() {
		$this->lang = "en";
	}
	
	function setLang($lang) {
		$this->lang = $lang;
	}
	
	function _($text, $replace=array()) {
		if(!file_exists(dirname(__FILE__)."/../languages/{$this->lang}.txt")) { $this->lang = "en"; }
		
		$file = file_get_contents(dirname(__FILE__)."/../languages/{$this->lang}.txt");
		
		preg_match("/\n{$text}=(.*)/", $file, $output);
		if(isset($output[1])) {
			foreach($replace as $key => $value) {
				$output[1] = str_ireplace($key, $value, $output[1]);
			}
			return $output[1];
		} else {
			$file = file_get_contents(dirname(__FILE__)."/../languages/en.txt");
		    
			preg_match("/\n{$text}=(.*)/", $file, $output);
			if(isset($output[1])) {
				foreach($replace as $key => $value) {
					$output[1] = str_ireplace($key, $value, $output[1]);
				}
				return $output[1];
			} else {
				return "Invalid Language string: ".$text;
			}
		}
	}
	
}
?>