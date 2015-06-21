<?php
namespace SendGrid;

/**
 * When a parameter is not of the expected type
 */
class Exception_Invalid_Object extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
