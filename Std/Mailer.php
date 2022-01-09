<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
	
	/** 
	 * \Of\Config
	 * system configuration 
	 */
	protected $_config;

	/* url class */
	protected $_url;
	
	/*
	*	holds an array of to or Cc or Bcc
	*	for later bulk mail
	*/
	protected $To = [];
	protected $Cc = [];
	protected $Bcc = [];
	
	/*
	*	sender email and name
	*/
	protected $from = ['name'=>'', 'email'=>''];
	
	/*
	*	email subject
	*/
	protected $subject = "Opoink say's hello";
	protected $tamplatePath;
	protected $message = "";

	/**
	 * attachment file paths
	 */
	protected $attachments = [];
	
	/*
	*	the header of the email
	*/
	protected $headers = [
		'mime' 			=> 'MIME-Version: 1.0',
		'contentType' 	=> 'Content-type: text/html; charset=iso-8859-1',
		'xPriority' 	=> 'X-Priority: 3 (Normal)',
		'To' 			=> '',
		'From'			=> '',
		'Cc'			=> '',
		'Bcc'			=> '',
	];
	
	public function __construct(
		\Of\Http\Url $Url
	){
		$this->_url = $Url;
		$this->_config = new \Of\Config();
	}
	
	/*
	*	set the email mime version
	*/
	public function setMime($mime=null){
		if($mime){
			$this->headers['mime'] = $mime;
		}
		return $this;
	}
	
	/*
	*	set the email Content-type
	*/
	public function setContentType($contentType=null){
		if($contentType){
			$this->headers['contentType'] = $contentType;
		}
		return $this;
	}
	
	/*
	*	add a recipient name and email
	*	@param email | the email address of recipient
	*	@param name | the name of recipient
	*	@param addressType | the type of address where to add 
	*			to or Cc or Bcc
	*/
	public function addAddress($email, $name='', $addressType='To'){
		$email = [
			'name' => $name,
			'email' => $email,
		];
		
		$this->$addressType[] = $email;
		return $this;
	}
	
	/*
	*	setsender name and email
	*	@param email | the email address of sender
	*	@param name | the name of sender
	*/
	public function setFrom($email, $name=''){
		$this->from['name'] = $name;
		$this->from['email'] = $email;
		return $this;
	}
	
	/*
	*	set the header To:
	*	return an array of email to use on mail($to)
	*/
	protected function setHeaderTo(){
		if(count($this->To) > 0){
			$headerTo = [];
			$To = [];
			foreach($this->To as $toVal){
				$headerTo[] = $toVal['name'] . " <" . $toVal['email'] . ">";
				$To[] = $toVal['email'];
			}
			$this->headers['To'] = 'To: ' . implode(",", $headerTo);
			return $To;
		} else {
			throw new \Exception('There is no recipient email address defined');
		}
	}
	
	/*
	*	set the header Cc or Bcc:
	*	@param type | Cc or Bcc
	*/
	protected function setHeaderCcOrBcc($type = 'Cc'){
		if(count($this->$type) > 0){
			$header = [];
			foreach($this->$type as $val){
				$header[] = $val['email'];
			}
			$this->headers[$type] = $type.': ' . implode(",", $header);
		} else {
			if(isset($this->headers[$type])){
				unset($this->headers[$type]);
			}
		}
	}
	
	/*
	*	set the subject of the email
	*/
	public function setSubject($subject){
		$this->subject = $subject;
		return $this;
	}
	
	/*
	*	@param | path to template
	*/
	public function setTemplatePath($path){
		$this->tamplatePath = $path;
		return $this;
	}
	
	/*
	*	set the message of the email
	*/
	public function setMessage($message){
		$this->message = $message;
		return $this;
	}
	
	public function getMessage(){
		if($this->tamplatePath && file_exists($this->tamplatePath)){
			ob_start();
				$content = $this->message;
				include($this->tamplatePath);
				$messageTemplate = ob_get_contents();
			ob_end_clean();
			
			return $messageTemplate;
		} else {
			return $this->message;
		}
	}

	/**
	 * add the path of the files to be attached
	 * @param $path 
	 */
	public function addAttachment($path, $name=null){
		if(is_file($path)){
			$this->attachments[] = [
				'name' => $name,
				'path' => $path
			];
		}
	}
	
	/*
	*	send the email to recipient
	*	return bool 
	*/
	public function send(){
		$usePhpmailer = $this->_config->getConfig('mailer/use_phpmailer');

		if($usePhpmailer){
			$this->sendViaPhpMailer();
		}
		else {
			try {
				$to = $this->setHeaderTo();
				$this->setHeaderCcOrBcc('Cc');
				$this->setHeaderCcOrBcc('Bcc');
				if($this->from['email'] != ''){
					$this->headers['From'] = 'From: '.$this->from['name'].' <'.$this->from['email'].'>' ;
				}
					
				$to = implode(",", $to);
				$head = implode("\r\n", $this->headers);
				
				return mail($to, $this->subject, $this->getMessage(), $head);
			} catch (\Exception $e) {
				return null;
			}
		}
	}

	public function sendViaPhpMailer(){
		//Create an instance; passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
			//Server settings
			$debug = $this->_config->getConfig('mailer/debug');
			$mail->SMTPDebug = false; 
			if($debug){
				$mail->SMTPDebug = SMTP::DEBUG_CONNECTION; //Enable verbose debug output
			}
			$mail->isSMTP();														// Send using SMTP

			$mail->Host       = $this->_config->getConfig('mailer/host');			// Set the SMTP server to send through
			$mail->SMTPAuth   = $this->_config->getConfig('mailer/auth');			// Enable SMTP authentication
			$mail->Username   = $this->_config->getConfig('mailer/username');		//SMTP username
			$mail->Password   = $this->_config->getConfig('mailer/password');		//SMTP password

			$smptSecure = $this->_config->getConfig('mailer/smpt_secure');
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			if($smptSecure){
				$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;					//Enable implicit TLS encryption
			}

			$mail->Port = $this->_config->getConfig('mailer/port');			//TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		
			//Recipients
			if($this->from['email'] != ''){
				$mail->setFrom($this->from['email'], $this->from['name']);
			}

			foreach($this->To as $toVal){
				$mail->addAddress($toVal['email'], $toVal['name']);
			}
			foreach($this->Cc as $toVal){
				$mail->addCC($toVal['email'], $toVal['name']);
			}
			foreach($this->Bcc as $toVal){
				$mail->addBCC($toVal['email'], $toVal['name']);
			}
			foreach ($this->attachments as $key => $value) {
				$mail->addAttachment($value['path'], $value['name']);				//Add attachments
			}
		
			//Content
			$mail->isHTML(true);													//Set email format to HTML
			$mail->Subject = $this->subject;
			$mail->Body    = $this->getMessage();
			// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
			$mail->send();
		} catch (\Exception $e) {
			return null;
		}
	}
}