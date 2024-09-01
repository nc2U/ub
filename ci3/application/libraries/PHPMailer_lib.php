<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class PHPMailer_lib {
		public function __construct() {
			require_once APPPATH . 'libraries/PHPMailer/src/Exception.php';
			require_once APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
			require_once APPPATH . 'libraries/PHPMailer/src/SMTP.php';
		}
		
		public function load() {
			return new PHPMailer(true);
		}
	}