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
		$script = basename($scriptPathname);
		if ($script !== private_function_sait_preference_script_name())
			throw new \RuntimeException('script name does not match script preferences');

		if (empty($_SERVER['HTTP_HOST']))
			throw new \RuntimeException('could not verify host match');
		$hA = parse_url($_SERVER['HTTP_HOST']);

		if (empty($_SERVER['REQUEST_URI']))
			throw new \RuntimeException('could not verify uri match');
		$pA = parse_url($_SERVER['REQUEST_URI']);
		if (empty($pA['path']))
			throw new \RuntimeException('could not verify uri match');
		$pathPath = dirname($pA['path']) .'/';

		$current = sprintf('%s:%s%s', $hA['host'], $hA['port'] ?? 80, $pathPath);

		$expectedA = private_function_sait_preference_host_port_path();
		$expected = sprintf('%s:%s%s', $expectedA['host'], $expectedA['port'] ?? 80, $expectedA['path']);

		if ($current !== $expected)
			throw new \RuntimeException('uri does not match script preferences');
	}
}
