<?php
namespace SendGrid;

/**
 * An exception thrown when SendGrid does not return a 200
 */
class Exception_Invalid_Response extends Exception
{
	public function __construct($data,$code)
	{
		parent::__construct("The Sendgrid API did not return the correct success code",$data);

		$this->code = $code;
	}
}
