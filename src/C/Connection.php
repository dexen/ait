<?php

class Connection
{
	protected $url;
	protected $h;

	function __construct(ScriptTuple $Remote)
	{
		$this->url = $Remote->url();
		$this->h = $this->connectToServer($Remote);
	}

	protected
	function connectToServer(ScriptTuple $Remote) #: resource
	{
		$h = curl_init($Remote->url());
		return $h;
	}

	function post($query = null)
	{
		curl_exec($this->h);
	}
}
