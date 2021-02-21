<?php

class ScriptTuple
{
	protected $site_url;
	protected $script_name;
	protected $datetime;
	protected $password;

	function __construct(array $rcd)
	{
		if (count($rcd) !== 4)
			throw new \LogicException(sprintf('expected a record of 4 items, got %d', count($rcd)));

		$this->site_url = $rcd[0];
		$this->script_name = $rcd[1];
		$this->datetime = $rcd[2];
		$this->password = $rcd[3];
	}

	function purgeSecrets()
	{
		$this->password = null;
	}

	function siteUrl() : string { return $this->site_url; }

	function scriptName() : string { return $this->script_name; }

	function scriptUrl() : string { return $this->site_url .$this->script_name; }

	function datetime() : string { return $this->datetime; }

	function pasword() : string
	{
			# if it's purged, an error will be raised - and that's nice.
		return $this->password;
	}
}
