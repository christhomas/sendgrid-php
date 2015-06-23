<?php
namespace SendGrid;

class Template
{
	protected $sendgrid;

	protected $url = array(
		"list"		=> "https://api.sendgrid.com/v3/templates",
		"get"		=> "https://api.sendgrid.com/v3/templates/(:template_id)",
		"create"	=> "https://api.sendgrid.com/v3/templates",
		"edit"		=> "https://api.sendgrid.com/v3/templates/(:template_id)/versions"
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

    public function create($name)
    {
    	$data = array("name"=>$name);

    	$template = $this->sendgrid->postRequest($this->url["create"],json_encode($data));

    	$template = $template->body;

    	return $template["id"];
    }

    public function editById($id_template,$name,$subject,$active,$html)
    {
		$data = array(
			"name"			=> $name,
			"subject"		=> $subject,
			"active"		=> intval($active),
			"html_content"	=> $html,
			"plain_content"	=> ""
		);

		if(strpos($data["subject"],"<%subject%>") === false){
			$data["subject"] .= "<%subject%>";
		}

		if(strpos($data["html_content"],"<%body%>") === false){
			$data["html_content"] .= "<%body%>";
		}

		if(strpos($data["plain_content"],"<%body%>") === false){
			$data["plain_content"] .= "<%body%>";
		}

		$url = $this->url["edit"];
		$url = str_replace("(:template_id)",$id_template,$url);

		$template = $this->sendgrid->postRequest($url,json_encode($data));
		$template = $template->body;

		if(array_key_exists("error",$template)){
			throw new Exception("Sendgrid error: {$template["error"]}");
		}

		return array($template["id"],$template["template_id"]);
    }

    public function delete($id_template)
    {
		//	TODO: add code that deletes a template
    }
}
