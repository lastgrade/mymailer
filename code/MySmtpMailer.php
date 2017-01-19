<?php
require_once "Mail.php";
include('Mail/mime.php');
//
class MySmtpMailer extends Mailer{
	
	var $mailer = null;
	
	private $sendDelay = 0;
	
	
	function __construct($mailer = null){
		parent::__construct();
		$this->mailer = $mailer;
	}
	
	protected function instanciate(){
		//due to throttling on some services (i.e. AWS SES) we should make the sender pause
		$host = defined('SMTPHOST') ? SMTPHOST : 'locahost';
		$port = defined('SMTPPORT') ? SMTPPORT : 1025;
		$username = defined('USERNAME') ? USERNAME : 'username';
		$password = defined('PASSWORD') ? PASSWORD : 'password';
	
		if($this->mailer == null){			
			$smtp = Mail::factory('smtp',
					array ('host' => $host,
							'auth' => false,
							'port'=> $port,
							'username' => $username,
							'password' => $password));
			$this->mailer = $smtp;
		}
	}
	
	
	/* Overwriting SilverStripe's Mailer function */
	function sendPlain($to, $from, $subject, $body, $attachedFiles = false, $customheaders = false){
		$this->instanciate();
		$headers = array ('From' => $from,
				'To' => $to,
				'Return-Path'   => $from,
				'Subject' => $subject);
		 
		return $this->sendMail($to, $headers, $body);
		
	}
	
	
	/* Overwriting SilverStripe's Mailer's function */
	function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false, $inlineImages = false){
		$this->instanciate();		
		
		$crlf = "\n";		
		$headers = array ('From' => $from,
				'To' => $to,
				'Return-Path'   => $from,
				'Subject' => $subject);
		
		// Creating the Mime message
		$mime = new Mail_mime($crlf);
		
		// Setting the body of the email
		$mime->setHTMLBody($htmlContent);
		
		$body = $mime->get();
		$headers = $mime->headers($headers);		
		
		return $this->sendMail($to, $headers, $body);
	}
	
	public function sendMail($to, $headers,  $body){
		$mail = $this->mailer->send($to, $headers, $body);
		
		if (PEAR::isError($mail)) {
			echo("<p>" . $mail->getMessage() . "</p>");
			die();
		} else {
			return true;
		}
		
		
	}
	
}