<?php

class ServerSecurity
{
	function __construct(string $scriptPathname)
	{
		$this->expectInstallationMatches($scriptPathname);
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

	function expectInstallationMatches(string $scriptPathname)
	{
		if ($this->currentScriptUrl() !== $this->preferenceScriptUrl())
			throw new \RuntimeException('url does not match script preferences');
	}

	private
	function currentScriptUrl() : string
	{
		if (empty($_SERVER['SERVER_PROTOCOL']))
			throw new \RuntimeException('unknown protocol');
		if (strncmp($_SERVER['SERVER_PROTOCOL'], 'HTTP/1', 6) === 0)
			$proto = 'http';
		else
			throw new \RuntimeException('unsupported protocol');
		if (($proto === 'http') && (!empty($_SERVER['HTTPS'])))
			$proto = 'https';
		if (empty($_SERVER['HTTP_HOST']))
			throw new \RuntimeException('unknown host');
		$hA = parse_url($_SERVER['HTTP_HOST']);

		if (empty($_SERVER['REQUEST_URI']))
			throw new \RuntimeException('unknown uri');
		$pA = parse_url($_SERVER['REQUEST_URI']);
		if (empty($pA['path']))
			throw new \RuntimeException('unknown uri');

		return sprintf('%s://%s:%s%s', $proto, $hA['host'], $hA['port'] ?? 80, $pA['path']);
	}

	protected
	function preferenceScriptUrl() : string
	{
		return private_function_sait_preference_script_url();
	}
}
