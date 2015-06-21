<?php
namespace SendGrid;

/**
 * When a parameter is not of the expected type
 */
class Exception_Invalid_Parameter extends Exception
{
	public function __construct($message,$data)
	{
		parent::__construct($message,$data);
	}
}
