<?php

class ServerSecurity
{
	function __construct()
	{
		session_start();
	}
	
	function loggedInP() { return ($_SESSION['_is_logged_in']??null) === true; }

		# crude!
	private
	function slowDownPasswordGuessingAttempts() { sleep(10); }

	private
	function passwordHash() : string { return private_function_password_hash(); }

	function performLogin(string $password)
	{
		$this->slowDownPasswordGuessingAttempts();

		password_verify($password, $this->passwordHash());
	}
}
