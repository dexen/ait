<?php

class Connection
{
	protected $site_url;
	protected $h;

	function __construct(ScriptTuple $Remote)
	{
		$this->site_url = $Remote->siteUrl();
		$this->h = $this->connectToServer($Remote);
	}

	protected
	function connectToServer(ScriptTuple $Remote) #: resource
	{
		$h = curl_init($Remote->scriptUrl());
		return $h;
	}

	function post($query = null)
	{
		curl_exec($this->h);
	}
}
