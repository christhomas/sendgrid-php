<?php

namespace SendGrid;

/**
 * An exception thrown when SendGrid does not return a 200
 */
class Exception extends \Exception
{
	protected $data;

	public function __construct($message,$data=array())
	{
		parent::__construct($message);

		$this->data = $data;
	}

	public function setValue($key,$value=null)
	{
		if(is_array($key) && $value === null){
			return $this->data = $key;
		}

		//	NOTE:	I'm not sure if I can throw an exception from inside this which is probably being
		//			handled inside a catch block....an exceptional inception?
		if(!is_string($key) || !strlen($key)){
			$key = "__ERROR_invalid_index";
		}

		return $this->data[$key] = $value;
	}

	public function getValue($key=null)
	{
		if($key === null){
			return $this->data;
		}

		//	NOTE:	I'm not sure if I can throw an exception from inside this which is probably being
		//			handled inside a catch block....an exceptional inception?
		if(!is_string($key) || !strlen($key)){
			$key = "__ERROR_invalid_index";
		}

		return array_key_exists($key,$this->data) ? $this->data[$key] : null;
	}

	public function getErrors()
	{
		return $this->data;
	}
}
