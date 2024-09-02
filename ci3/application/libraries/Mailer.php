<?php
	use PHPMailer\PHPMailer\PHPMailer;
	
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Mailer {
		public function __construct() {
			require_once APPPATH . 'libraries/PHPMailer/src/Exception.php';
			require_once APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
			require_once APPPATH . 'libraries/PHPMailer/src/SMTP.php';
		}
		
		public function load() {
			$mail = new PHPMailer(true);
			// 서버 설정
			$mail->isSMTP();
			$mail->Host = getenv('EMAIL_HOST');
			$mail->SMTPAuth = true;
			$mail->Username = getenv('EMAIL_USER');
			$mail->Password =  getenv('EMAIL_PASS');
			$mail->SMTPSecure = getenv('EMAIL_CRYPTO') ?: 'tls'; // 또는 'ssl'
			$mail->Port = getenv('EMAIL_PORT') ?: 587; // 또는 465
			$mail->isHTML(true);
			return $mail;
		}
		
		public function clear()
		{
			return NULL;
		}
	}