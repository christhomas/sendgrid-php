<?php
namespace SendGrid;

class Template
{
	protected $sendgrid;

	protected $url = array(
		"list"	=> "https://api.sendgrid.com/v3/templates",
		"get"	=> "https://api.sendgrid.com/v3/templates/(:template_id)"
	);

    public function __construct($sendgrid)
    {
		if(!$sendgrid instanceof \SendGrid){
			throw new Exception_Invalid_Object("SendGrid object was not valid");
		}

		$this->sendgrid = $sendgrid;
    }

    public function getList()
    {
    	$list = $this->sendgrid->getRequest($this->url["list"]);
    	$list = $list->body["templates"];

    	return $list;
    }

    public function getById($id_template)
    {
    	$url = $this->url["get"];
    	$url = str_replace("(:template_id)",$id_template,$url);

    	$template = $this->sendgrid->getRequest($url);

    	return $template->body;
    }
}
