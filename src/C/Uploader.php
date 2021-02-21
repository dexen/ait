<?php

class Uploader
{
	protected $Connection;

	function __construct(Connection $Connection)
	{
		$this->Connection = $Connection;
	}

	function postFile(string $pathname, int $mtime, int $perms)
	{
		$file = curl_file_create($pathname);
		$this->Connection->post(null, compact('file', 'mtime', 'perms'));
	}
}
