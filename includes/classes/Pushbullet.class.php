<?php
class PushError extends Exception { }

class Pushbullet {
	private $Token;
	public $Email;
	public $Valid = false;
	private $APIVersion = "https://api.pushbullet.com/v2";
	
	public function __construct($token = "") {
		if(empty($token)) {
			throw new PushError("Access token is missing.");
		} else {
			$this->Token = $token;
			
			$validate = $this->Validate();
			if($validate != false) {
				$this->Email = $validate->email;
				$this->Valid = true;
			} else {
				throw new PushError("Pushbullet account is invalid.");
			}
		}
	}
	
	private function cURLRequest($url, $method="GET", $post=array()) {
		$header = array();
		$header[] = "Access-Token: " . $this->Token;
		$header[] = "Content-Type: application/json";
		
        $ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $this->APIVersion . $url); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if($method == "POST") {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
			curl_setopt($ch, CURLOPT_POST, 1);
		}
		
        $output = curl_exec($ch); 
        curl_close($ch);
		
		return $output;
	}
	
	public function Validate() {
		$output = json_decode($this->cURLRequest("/users/me"));
		if(isset($output->error)) {
			return false;
		} else {
			return $output;
		}
	}
	
	public function PushNote($email="", $title="", $body="") {
		if($this->Valid == true && !empty($email) || !empty($title) || !empty($title)) {
			$output = $this->cURLRequest(
				"/pushes",
				"POST",
				array(
					"email" => $email,
					"type" => "note",
					"title" => $title,
					"body" => $body
				)
			);
			
			if(!isset(json_decode($output)->error)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	public function PushLink($email="", $link="", $title="", $body="") {
		if($this->Valid == true && !empty($email) || !empty($title) || !empty($title)) {
			$output = $this->cURLRequest(
				"/pushes",
				"POST",
				array(
					"email" => $email,
					"type" => "link",
					"title" => $title,
					"body" => $body,
					"url" => $link
				)
			);
			
			if(!isset(json_decode($output)->error)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}
?>