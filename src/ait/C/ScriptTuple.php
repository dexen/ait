<?php

class ScriptTuple
{
	protected $url;
	protected $script_name;
	protected $password;

	function __construct(array $rcd)
	{
		if (count($rcd) !== 3)
			throw new \LogicException(sprintf('expected a record of 3 items, got %d', count(rcd)));

		$this->url = $rcd[0];
		$this->script_name = $rcd[1];
		$this->password = $rcd[2];
	}

	function purgeSecrets()
	{
		$this->password = null;
	}

	function url() : string { return $this->url; }

	function scriptName() : string { return $this->script_name; }

	function pasword() : string
	{
			# if it's purged, an error will be raised - and that's nice.
		return $this->password;
	}
}
