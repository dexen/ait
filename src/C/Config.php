<?php

	# file format:
	# records in the form of:
	# WEBSITE_ROOT_URL DELIMITER AIT_SCRIPT_NAME DELIMITER DATETIME DELIMITER PASSWORD
class Config
{
	const DELIMITER = "\t";
	protected $pathname;

	function __construct(string $pathname)
	{
		if (!file_exists($pathname))
			throw new \RuntimeException(sprintf('config file "%s" not found', $pathname));
		$this->pathname = $pathname;
	}

	function configForServer(string $url) : ScriptTuple
	{
		$a = explode("\n", file_get_contents($this->pathname));
		foreach ($a as $line)
			if (explode(static::DELIMITER, $line, 4)[0] === $url)
				return new ScriptTuple(explode(static::DELIMITER, $line, 4));
		throw new \RuntimeException(sprintf('configuration not found for server "%s"', $url));
	}
}
