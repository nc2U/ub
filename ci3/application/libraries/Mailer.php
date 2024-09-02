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
			$mail->SMTPAuth = true;
			$mail->Host = getenv('EMAIL_HOST');
			$mail->Username = getenv('EMAIL_USER');
			$mail->Password =  getenv('EMAIL_PASS');
			$mail->Port = getenv('EMAIL_PORT') ?: 587; // 또는 465
			$mail->SMTPSecure = getenv('EMAIL_CRYPTO') ?: 'tls'; // 또는 'ssl'
			$mail->isHTML(true);
			return $mail;
		}
	}