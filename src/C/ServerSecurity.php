<?php

class ServerSecurity
{
	function __construct()
	{
		$this->expectInstallationMatches();
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

		if (password_verify($password, $this->passwordHash()))
			$_SESSION['_is_logged_in'] = true;
	}

	function expectInstallationMatches()
	{
		if (empty($_SERVER['HTTP_HOST']))
			throw new \RuntimeException('could not verify host match');
		if ($_SERVER['HTTP_HOST'] !== private_function_sait_preference_host())
			throw new \RuntimeException('host does not match script preferences');

		if (empty($_SERVER['REQUEST_URI']))
			throw new \RuntimeException('could not verify uri match');
		$a = parse_url($_SERVER['REQUEST_URI']);
		if (empty($a['path']))
			throw new \RuntimeException('could not verify uri match');
		if (rawurldecode($a['path']) !== private_function_sait_preference_uri())
			throw new \RuntimeException('uri does not match script preferences');
	}
}
