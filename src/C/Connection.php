<?php

class Connection
{
	protected $h;
	protected $Remote;

	function __construct(ScriptTuple $Remote)
	{
		$this->h = $this->connectToServer($Remote);
		$Remote->purgeSecrets();
		$this->Remote = $Remote;
	}

	protected
	function connectToServer(ScriptTuple $Remote) #: resource
	{
		$h = curl_init($Remote->scriptUrl());
		curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
		return $h;
	}

	function post($query = null, $payload = null)
	{
		curl_setopt($this->h, CURLOPT_POST, 1);
		curl_setopt($this->h, CURLOPT_POSTFIELDS, $payload);
		$ev = curl_exec($this->h);
		$info = curl_getinfo($this->h);
		switch ($info['http_code']) {
		case 404:
			throw new AitException(sprintf('sait script "%s" not found on site "%s"', $this->Remote->scriptName(), $this->Remote->siteUrl(), ));
		case 200:
			return;
		default:
			throw new AitException(sprintf('server returned HTTP status "%s"', $info['http_code'])); }
	}
}
