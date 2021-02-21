<?php

class Uploader
{
	protected $Connection;

	function __construct(Connection $Connection)
	{
		$this->Connection = $Connection;
	}

	function postFile(string $pathname, string $content, int $mtime, int $perms)
	{
		$this->Connection->post();
	}
}
