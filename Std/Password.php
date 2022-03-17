<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Password {
	
	protected $password;
	protected $passwordHash;
	
	public function setPassword($password=""){
		$this->password = $password;
		return $this;
	}
	
	public function setHash($passwordHash=""){
		$this->passwordHash =  $passwordHash;
		return $this;
	}
	
	/**
		PASSWORD_BCRYPT
		PASSWORD_BCRYPT
		PASSWORD_ARGON2_DEFAULT_MEMORY_COST
		PASSWORD_ARGON2_DEFAULT_TIME_COST
		ASSWORD_ARGON2_DEFAULT_THREADS
		PASSWORD_DEFAULT
	*/
	public function getHash($algo=PASSWORD_DEFAULT, $options=[]){
		return password_hash($this->password, $algo, $options);
	}

	/*
	*	return bool true | false
	*	return null if not set
	*/
	public function verify(){
		if(!$this->password || !$this->passwordHash){
			return null;
		}
		
		if (password_verify($this->password, $this->passwordHash)) {
			return true;
		} else {
			return false;
		}
	}

	/*
	*	confirm password
	*	check if the password and confirm password match
	*	return bool 
	*/
	public function confirmPassword($password, $confirmPassword){
		/*
		*	set the first password to get the hashed pawword
		*/
		$this->password = $password;
		$this->passwordHash = $this->getHash();
		
		/*
		*	then set $this->password to $confirmPassword
		*	for verify() 
		*/
		$this->password = $confirmPassword;
		return $this->verify();
	}	
	
	/**
	 * generate password with default length of 10 character long
	 * this generator came from 
	 * https://stackoverflow.com/questions/6101956/generating-a-random-password-in-php
	 */
	public static function generate($length=10) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}
}
?>