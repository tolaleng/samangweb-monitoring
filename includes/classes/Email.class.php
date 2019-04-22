<?php
class Email {
	public function Send($to, $subject, $message) {
		global $config, $db;
		$sql = $db->query("SELECT * FROM mail_settings");
		$row = $sql->fetch_assoc();
		
		if($row['mail_type'] == "php") {
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			$headers .= 'From: '.$config['name'].' <'.$row['php_mail'].'>' . "\r\n";

			mail($to, $subject, $message, $headers);
		} else {
			$mail = new PHPMailer;
			
			$mail->isSMTP();
			$mail->Host = $row['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $row['smtp_username'];
			$mail->Password = base64_decode($row['smtp_password']);
			$mail->SMTPSecure = 'tls';
			$mail->Port = $row['smtp_port'];

			$mail->setFrom($row['smtp_from'], $config['name']);
			$mail->addAddress($to);

			$mail->isHTML(true);

			$mail->Subject = $subject;
			$mail->Body    = $message;

			$mail->send();
		}
	}
}
?>